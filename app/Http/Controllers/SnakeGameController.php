<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameTypes;
use App\Models\MatchmakingQueue;
use App\Models\Transaction;
use App\Models\User;
use App\Services\OneSignalService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SnakeGameController extends Controller
{

    public function checkStatusCron()
    {
        DB::transaction(function () {
            $games = Game::where('game', 'snake')
                ->where('status', 'playing')
                ->lockForUpdate()
                ->get();

            foreach ($games as $game) {
                $isBotGame = $game->game_type === 'Bot';
                $url = $isBotGame
                    ? 'https://ludo.tzsmm.com/api/bot-snake-game/status'
                    : 'https://ludo.tzsmm.com/api/snake-games/status';

                $response = Http::withHeaders([
                    'accept' => 'application/json',
                ])->post($url, [
                            'api_key' => settings('tzludo_api_key'),
                            'game_id' => $game->api_game_id,
                        ]);

                if (!$response->successful()) {
                    Log::error("Snake game API failed for game {$game->id}: " . $response->body());
                    continue;
                }

                $data = $response->json();

                if (!isset($data['status'], $data['game_status'])) {
                    Log::error("Invalid API response for snake game {$game->id}: " . json_encode($data));
                    continue;
                }

                if (!$data['status']) {
                    Log::warning("Snake game API returned status=false for game {$game->id}: " . ($data['message'] ?? 'No message'));
                    continue;
                }

                if ($data['game_status'] === 'finished') {
                    $winnerId = null;
                    $loserId = null;

                    if ($isBotGame) {
                        $user = User::find($game->player_1);
                        $isUserWinner = $data['winner'] === 'user';

                        if ($isUserWinner && $user) {
                            $user->increment('winning_balance', $game->winning);
                            Transaction::create([
                                'user_id' => $user->id,
                                'type' => 'credit',
                                'action' => 'Won Snake Bot Match #' . $game->id,
                                'amount' => $game->winning,
                                'extra' => json_encode(['game_id' => $game->id, 'game' => 'snake']),
                            ]);

                            if ($user->one_signal) {
                                $msg = "🎉 You beat the bot in Snake Match #{$game->id}!\nWinnings: BDT " . number_format($game->winning, 2);
                                OneSignalService::sendNotification('Victory!', $msg, $user->one_signal);
                            }

                            $game->update([
                                'status' => 'finished',
                                'winner_id' => $user->id,
                                'is_bot_winner' => 'No',
                            ]);
                        } elseif ($user) {
                            if ($user->one_signal) {
                                $msg = "😢 Bot won your Snake Match #{$game->id}. Try again!";
                                OneSignalService::sendNotification('Bot Wins', $msg, $user->one_signal);
                            }

                            $game->update([
                                'status' => 'finished',
                                'winner_id' => null,
                                'is_bot_winner' => 'Yes',
                            ]);
                        }

                    } else {
                        // PvP Match
                        $apiWinner = $data['winner'] ?? null;

                        if ($apiWinner == $game->player_1_api_id) {
                            $winnerId = $game->player_1;
                            $loserId = $game->player_2;
                        } elseif ($apiWinner == $game->player_2_api_id) {
                            $winnerId = $game->player_2;
                            $loserId = $game->player_1;
                        }

                        $winner = User::find($winnerId);
                        $loser = User::find($loserId);

                        if ($winner) {
                            $winner->increment('winning_balance', $game->winning);
                            Transaction::create([
                                'user_id' => $winner->id,
                                'type' => 'credit',
                                'action' => 'Won Snake Match #' . $game->id,
                                'amount' => $game->winning,
                            ]);

                            if ($winner->one_signal) {
                                $msg = "🎉 Congrats {$winner->name}, you won Snake Match #{$game->id}!\nPrize: BDT " . number_format($game->winning, 2);
                                OneSignalService::sendNotification('You Won!', $msg, $winner->one_signal);
                            }
                        }

                        if ($loser && $loser->one_signal) {
                            $msg = "💔 You lost Snake Match #{$game->id}. Keep trying!";
                            OneSignalService::sendNotification('Match Result', $msg, $loser->one_signal);
                        }

                        $game->update([
                            'status' => 'finished',
                            'winner_id' => $winnerId,
                            'is_bot_winner' => null,
                        ]);
                    }

                    Log::info("Snake game #{$game->id} finished. Winner: " . ($winnerId ?? 'Bot'));

                } elseif ($data['game_status'] === 'canceled') {
                    $players = [$game->player_1, $game->player_2];
                    foreach ($players as $pid) {
                        $user = User::find($pid);
                        if (!$user)
                            continue;

                        $user->increment('deposit_balance', $game->joining);

                        Transaction::create([
                            'user_id' => $user->id,
                            'type' => 'credit',
                            'action' => 'Canceled Snake Match',
                            'amount' => $game->joining,
                        ]);

                        if ($user->one_signal) {
                            $msg = "⚠️ Snake Match #{$game->id} was canceled.\nRefunded: BDT " . number_format($game->joining, 2);
                            OneSignalService::sendNotification('Match Canceled', $msg, $user->one_signal);
                        }
                    }

                    $game->update(['status' => 'canceled']);
                    Log::info("Snake game #{$game->id} canceled and refunded.");
                }
            }
        });
    }


    public function createCustomGameType(Request $request)
    {
        $request->validate([
            'joining' => 'required|numeric|min:' . settings('snake_minimum')
        ]);

        $user = user();
        $joining = $request->joining;
        $winning = round($joining * settings('snake_winning'), 0);
        $lockKey = "create_snake_game_user_{$user->id}";

        $lock = Cache::lock($lockKey, 10); // lock for 10 seconds max

        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Please wait, your last match request is being processed.');
        }
        if ($joining > $user->deposit_balance) {
            return redirect()->route('addfund')->with('error', 'Insufficient balance to create match.');
        }
        if (GameTypes::where('created_by', $user->id)->where('game', 'snake')->exists()) {
            return redirect()->back()->with('error', 'First finish your previous game.');
        }

        DB::transaction(function () use ($user, $joining, $winning) {
            $user->decrement('deposit_balance', $joining);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'action' => 'Created Snake Match',
                'amount' => $joining,
                'created_at' => now(),
            ]);

            $type = GameTypes::create([
                'game' => 'snake',
                'joining' => $joining,
                'winning' => $winning,
                'created_by' => $user->id,
                'player' => 2,
                'title' => 'Join Snake Match',
            ]);

            MatchmakingQueue::create([
                'user_id' => $user->id,
                'game_type_id' => $type->id,
                'status' => 'waiting',
                'game' => 'snake',
                'queued_at' => now(),
            ]);

            if ($user->one_signal) {
                $formatted = number_format($joining, 2);
                $time = now()->format('h:i A, M d, Y');
                $msg = "Hey {$user->name},\n\n" .
                    "You've created a Snake match! 🐍🎲\n\n" .
                    "💰 Entry Fee: BDT {$formatted}\n🕒 Time: {$time}\n\n" .
                    "Wait for an opponent to join!";

                OneSignalService::sendNotification('Match Created!', $msg, $user->one_signal);
            }
        });

        return redirect()->back()->with('success', 'Match Created Successfully');
    }



    public function matchmakingCron()
    {
        DB::transaction(function () {
            $queues = MatchmakingQueue::where('status', 'waiting')
                ->where('game', 'snake')
                ->select('game_type_id', DB::raw('count(*) as total'))
                ->groupBy('game_type_id')
                ->having('total', '>=', 2)
                ->get();

            foreach ($queues as $queue) {
                $gameTypeId = $queue->game_type_id;

                $gameType = GameTypes::find($gameTypeId);
                if (!$gameType) {
                    Log::warning("Invalid game_type_id: {$gameTypeId}");
                    continue;
                }

                $players = MatchmakingQueue::where('game_type_id', $gameTypeId)
                    ->where('status', 'waiting')
                    ->where('game', 'snake')
                    ->orderBy('created_at')
                    ->limit(2)
                    ->lockForUpdate()
                    ->get();

                if ($players->count() === 2) {
                    $player1 = $players[0];
                    $player2 = $players[1];

                    if ($player1->status !== 'waiting' || $player2->status !== 'waiting') {
                        Log::warning("Players {$player1->user_id} or {$player2->user_id} are no longer waiting for game_type_id {$gameTypeId}");
                        continue;
                    }
                    $endTime = Carbon::now('Asia/Dhaka')
                        ->addMinutes(5)
                        ->toDateTimeString();
                    Log::info($endTime);
                    $apiResponse = Http::post('https://ludo.tzsmm.com/api/snake-create-game', [
                        'api_key' => settings('tzludo_api_key'),
                        'end_time' => $endTime,
                        'win_url' => route('win'),
                        'lose_url' => route('lose'),
                        'home_url' => route('cancel'),
                    ]);

                    if ($apiResponse->successful()) {
                        $apiData = $apiResponse->json();

                        if (!($apiData['status'])) {
                            Log::error("API failed game_type_id {$gameTypeId}: " . $apiData['message']);
                            continue;
                        }

                        Game::create([
                            'game_type_id' => $gameTypeId,
                            'player_1' => $player1->user_id,
                            'player_2' => $player2->user_id,
                            'api_game_id' => $apiData['game_id'],
                            'player_1_api_id' => $apiData['player_1_id'],
                            'player_2_api_id' => $apiData['player_2_id'],
                            'player_1_url' => $apiData['player_1_url'],
                            'player_2_url' => $apiData['player_2_url'],
                            'status' => 'playing',
                            'joining' => $gameType->joining,
                            'game' => $gameType->game,
                            'winning' => $gameType->winning,
                        ]);

                        // Send notifications
                        $user1 = User::find($player1->user_id);
                        $user2 = User::find($player2->user_id);

                        $msg = "Hey {name},\n\nYour Snake and Ladders game has started! 🎲🐍\n\n" .
                            "Tap the app to join and start playing!\n\nBest of luck!";

                        if ($user1 && $user1->one_signal) {
                            OneSignalService::sendNotification(
                                'Game Started!',
                                str_replace('{name}', $user1->name, $msg),
                                $user1->one_signal,
                                null,
                                [['id' => 'open_app', 'text' => 'Join Game']]
                            );
                        }

                        if ($user2 && $user2->one_signal) {
                            OneSignalService::sendNotification(
                                'Game Started!',
                                str_replace('{name}', $user2->name, $msg),
                                $user2->one_signal,
                                null,
                                [['id' => 'open_app', 'text' => 'Join Game']]
                            );
                        }

                        Log::info("Match created for players {$player1->user_id} and {$player2->user_id} with game ID {$apiData['game_id']}");
                        $gameType->delete();
                        MatchmakingQueue::whereIn('id', [$player1->id, $player2->id])->delete();
                    } else {
                        Log::error("API call failed for game_type_id {$gameTypeId}: " . $apiResponse->body());
                    }
                }
            }
        });

        if (settings('snake_bot_status') === 'on') {
            $botEligibleQueues = MatchmakingQueue::where('status', 'waiting')
                ->where('game', 'snake')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->where('created_at', '<=', now()->subMinutes(3))
                ->get();

            $stats = $this->calculateBotStatistics();
            $currentBotWinCount = $stats['bot_win_count'];
            $totalBotGames = $stats['total_bot_games'];
            $targetBotWinPercentage = settings('snake_bot_winning'); // e.g., 60

            foreach ($botEligibleQueues as $queue) {
                $gameType = GameTypes::find($queue->game_type_id);
                if (!$gameType) {
                    Log::warning("Invalid game_type_id for Snake bot match: {$queue->game_type_id}");
                    continue;
                }

                $user = User::find($queue->user_id);
                if (!$user) {
                    Log::warning("User not found for Snake queue ID: {$queue->id}");
                    continue;
                }

                // Randomize bot match time between 3:20–4:40 minutes
                $randomTime = rand(200, 280); // 3:20–4:40 minutes in seconds
                $queueAge = Carbon::parse($queue->created_at)->diffInSeconds(now());
                if ($queueAge < $randomTime) {
                    Log::info("Snake queue ID {$queue->id} not yet eligible for bot match (age: {$queueAge}s, required: {$randomTime}s)");
                    continue;
                }

                // Decide if bot should win
                $potentialProfit = $gameType->joining - $gameType->winning;
                $futureTotalProfit = $stats['total_bot_wins'] + $potentialProfit - $stats['total_bot_losses'];
                $futureTotalVolume = $stats['total_bot_wins'] + $stats['total_bot_losses'] + abs($potentialProfit);

                $futureProfitPercentage = $futureTotalVolume > 0
                    ? ($futureTotalProfit / $futureTotalVolume) * 100
                    : 0;

                Log::info('Snake Bot Win Decision', [
                    'queue_id' => $queue->id,
                    'game_type_id' => $queue->game_type_id,
                    'user_id' => $queue->user_id,
                    'joining_fee' => $gameType->joining,
                    'winning_amount' => $gameType->winning,
                    'potential_profit' => $potentialProfit,
                    'total_bot_wins' => $stats['total_bot_wins'],
                    'total_bot_losses' => $stats['total_bot_losses'],
                    'future_total_profit' => $futureTotalProfit,
                    'future_total_volume' => $futureTotalVolume,
                    'future_profit_percentage' => round($futureProfitPercentage, 2),
                    'target_bot_win_percentage' => $targetBotWinPercentage,
                    'decision' => $futureProfitPercentage < $targetBotWinPercentage ? 'bot' : 'player',
                ]);

                $autoWin = ($futureProfitPercentage < $targetBotWinPercentage) ? 'bot' : 'player';
                $isBotWinner = $autoWin === 'bot' ? 'Yes' : 'No';

                $endTime = Carbon::now('Asia/Dhaka')
                    ->addMinutes(5)
                    ->toDateTimeString();

                $apiResponse = Http::get('https://ludo.tzsmm.com/api/bot-snake-game/create', [
                    'api_key' => settings('tzludo_api_key'),
                    'auto_win' => $autoWin,
                    'end_time' => $endTime,
                    'win_url' => route('win'),
                    'lose_url' => route('lose'),
                    'home_url' => route('cancel'),
                ]);

                if ($apiResponse->successful()) {
                    $apiData = $apiResponse->json();

                    if (!($apiData['status'] ?? false)) {
                        Log::error("Snake Bot API failed for game_type_id {$queue->game_type_id}: " . ($apiData['message'] ?? 'Unknown error'));
                        continue;
                    }

                    Game::create([
                        'game_type_id' => $queue->game_type_id,
                        'player_1' => $queue->user_id,
                        'player_2' => null,
                        'api_game_id' => $apiData['game_id'],
                        'player_1_api_id' => $apiData['player_id'],
                        'player_2_api_id' => null,
                        'player_1_url' => $apiData['player_url'],
                        'player_2_url' => null,
                        'status' => 'playing',
                        'joining' => $gameType->joining,
                        'game' => $gameType->game,
                        'winning' => $gameType->winning,
                        'game_type' => 'Bot',
                        'is_bot_winner' => $isBotWinner,
                    ]);

                    if ($user && $user->one_signal) {
                        $msg = "Hey {$user->name},\n\nYour Snake and Ladders game has started! 🎲🐍\n\n" .
                            "Tap the app to join and start playing!\n\nBest of luck!";
                        OneSignalService::sendNotification(
                            'Game Started!',
                            $msg,
                            $user->one_signal,
                            null,
                            [['id' => 'open_app', 'text' => 'Join Game']]
                        );
                    }

                    Log::info("Snake bot match created for player {$queue->user_id} with game ID {$apiData['game_id']} for game_type_id {$queue->game_type_id}, bot_winner: {$isBotWinner}");
                    $queue->delete();
                } else {
                    Log::error("Snake Bot API call failed for game_type_id {$queue->game_type_id}: " . $apiResponse->body());
                }
            }
        }

        // Auto-cancel and refund stale matches
        $expiredQueues = MatchmakingQueue::where('status', 'waiting')
            ->where('game', 'snake')
            ->whereHas('gameType', function ($q) {
                $q->where('created_at', '<=', now()->subMinutes(5));
            })
            ->get();

        foreach ($expiredQueues->groupBy('game_type_id') as $gameTypeId => $queues) {
            $gameType = GameTypes::find($gameTypeId);
            if (!$gameType)
                continue;

            foreach ($queues as $queue) {
                $user = User::find($queue->user_id);
                if ($user) {
                    $user->increment('deposit_balance', $gameType->joining);
                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'credit',
                        'action' => 'Auto Canceled Snake And Ladders Match',
                        'amount' => $gameType->joining,
                        'created_at' => now(),
                    ]);

                    if ($user->one_signal) {
                        $msg = "Hey {$user->name},\n\nYour Snake And Ladders match was automatically canceled due to timeout.\n💵 Refunded: BDT " . number_format($gameType->joining, 2) . "\n🕒 " . now()->format('h:i A, M d') . "\n\nAmount has been credited back.";
                        OneSignalService::sendNotification('Match Auto Canceled & Refunded', $msg, $user->one_signal);
                    }
                }

                $queue->delete();
            }

            $gameType->delete();
            \Log::info("Auto-canceled and refunded game_type_id: {$gameTypeId}");
        }
    }
    public function calculateBotStatistics()
    {
        $today = Carbon::today();

        // Total bot wins today
        $totalBotWins = Game::where('game_type', 'Bot')
            ->where('game', 'snake')
            ->where('status', 'finished')
            ->whereNot('status', 'canceled')
            ->where('is_bot_winner', 'Yes')
            ->whereDate('created_at', $today)
            ->sum('joining');

        // Total bot losses today
        $totalBotLosses = Game::where('game_type', 'Bot')
            ->where('game', 'snake')
            ->where('is_bot_winner', 'No')
            ->whereNot('status', 'canceled')
            ->whereDate('created_at', $today)
            ->sum('winning') - Game::where('game_type', 'Bot')
                ->where('game', 'snake')
                ->where('is_bot_winner', 'No')
                ->whereDate('created_at', $today)
                ->sum('joining');

        // Total bot games today
        $totalBotGames = Game::where('game_type', 'Bot')
            ->where('game', 'snake')
            ->whereDate('created_at', $today)
            ->count();

        // Bot win count today
        $botWinCount = Game::where('game_type', 'Bot')
            ->where('game', 'snake')
            ->where('is_bot_winner', 'Yes')
            ->whereDate('created_at', $today)
            ->count();

        // Bot win percentage today
        $botWinPercentage = $totalBotGames > 0
            ? ($botWinCount / $totalBotGames) * 100
            : 0;

        // Bot loss percentage today
        $botLossPercentage = $totalBotGames > 0
            ? (($totalBotGames - $botWinCount) / $totalBotGames) * 100
            : 0;

        // Expected bot win percentage from settings
        $expectedBotWinPercentage = (float) settings('snake_bot_winning');

        // Bot profit today
        $botProfit = $totalBotWins - $totalBotLosses;

        return [
            'total_bot_wins' => $totalBotWins,
            'total_bot_losses' => $totalBotLosses,
            'total_bot_games' => $totalBotGames,
            'bot_win_count' => $botWinCount,
            'bot_win_percentage' => round($botWinPercentage, 2),
            'bot_loss_percentage' => round($botLossPercentage, 2),
            'expected_bot_win_percentage' => $expectedBotWinPercentage,
            'bot_profit' => $botProfit,
        ];
    }

    public function checkMatch(Request $request)
    {
        $userId = user('id');
        $game = Game::where('game_type_id', $request->game_type_id)->where(function ($query) use ($userId) {
            $query->where('player_1', $userId)->orWhere('player_2', $userId);
        })->where('status', 'playing')->first();

        if ($game) {
            $opponentId = $game->player_1 == $userId ? $game->player_2 : $game->player_1;
            $opponent = User::find($opponentId);
            $playerUrl = $game->player_1 == $userId ? $game->player_1_url : $game->player_2_url;

            return response()->json([
                'status' => 'matched',
                'game_id' => $game->id,
                'player_url' => $playerUrl,
                'opponent' => [
                    'id' => $opponent->id,
                    'name' => $opponent->name ?? 'Opponent',
                    'avatar' => asset($opponent->avatar ?? 'https://www.svgrepo.com/show/384674/account-avatar-profile-user-11.svg'),
                ],
            ]);
        }

        return response()->json(['status' => 'waiting']);
    }


    public function joinMatch($id)
    {
        $game_type_id = $id;
        $lockKey = "join_match_snake_{$game_type_id}";
        $matchfull_key = 'snake_match_full_' . $game_type_id;

        $lock = Cache::lock($lockKey, 10);
        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Please wait, already processing.');
        }

        try {
            if (Cache::has($matchfull_key)) {
                return redirect()->back()->with('error', 'This match is already full.');
            }

            $gameType = GameTypes::find($game_type_id);
            if (!$gameType) {
                return redirect()->route('dashboard')->with('error', 'Invalid game type.');
            }

            $userId = user('id');
            $user = user();

            $game = Game::where('game_type_id', $game_type_id)
                ->where(function ($query) use ($userId) {
                    $query->where('player_1', $userId)->orWhere('player_2', $userId);
                })
                ->where('status', 'playing')
                ->first();

            if ($game) {
                $playerUrl = $game->player_1 == $userId ? $game->player_1_url : $game->player_2_url;
                return redirect($playerUrl);
            }

            // Already queued
            $existingQueue = MatchmakingQueue::where('user_id', $userId)
                ->where('game_type_id', $game_type_id)
                ->where('status', 'waiting')
                ->exists();

            if ($existingQueue) {
                return redirect()->route('games.snake.waiting', $game_type_id);
            }

            // Balance check
            if ($gameType->joining > $user->deposit_balance) {
                return redirect()->route('addfund')->with('error', "You must have at least {$gameType->joining} in your account to play this match. Please deposit.");
            }

            // Process join
            DB::transaction(function () use ($user, $gameType, $game_type_id, $matchfull_key) {
                $joinedCount = MatchmakingQueue::where('game_type_id', $game_type_id)
                    ->where('game', 'snake')
                    ->where('status', 'waiting')
                    ->count();

                if ($joinedCount >= 2) {
                    throw new \Exception('Match is already full.');
                }

                $user->decrement('deposit_balance', $gameType->joining);

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'action' => 'Joined Snake and Ladders Match',
                    'amount' => $gameType->joining,
                    'created_at' => now(),
                ]);

                MatchmakingQueue::create([
                    'user_id' => $user->id,
                    'game_type_id' => $game_type_id,
                    'status' => 'waiting',
                    'game' => 'snake',
                    'queued_at' => now(),
                ]);

                if ($joinedCount + 1 >= 2) {
                    Cache::put($matchfull_key, true, 60);
                }

                // Notification
                if ($user->one_signal) {
                    $formattedAmount = number_format($gameType->joining, 2);
                    $time = now()->format('h:i A, M d, Y');

                    $message = "Hey {$user->name},\n\n" .
                        "You’ve joined a Snake and Ladders match! 🐍🎲\n\n" .
                        "💰 Entry Fee: BDT {$formattedAmount}\n🕒 Time: {$time}\n\n" .
                        "Get ready, your match will start shortly!";

                    OneSignalService::sendNotification(
                        'Match Joined!',
                        $message,
                        $user->one_signal
                    );
                }
            });

            return redirect()->back()->with('success', 'Match Joined Successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } finally {
            $lock->release();
        }
    }


    public function getSnakeQueueStatus()
    {
        $userId = user('id');
        $waitingTypes = GameTypes::where('game', 'snake')
            ->orderByRaw('CAST(joining AS DECIMAL(10,2)) ASC')
            ->get();

        $playingGames = Game::with(['player1', 'player2'])
            ->where('game', 'snake')
            ->where('status', 'playing')
            ->get();

        $response = [];

        foreach ($waitingTypes as $type) {
            $hasActive = Game::where('game_type_id', $type->id)
                ->where('status', 'playing')
                ->exists();

            if ($hasActive)
                continue;

            $match = MatchmakingQueue::with('user')
                ->where('game_type_id', $type->id)
                ->where('status', 'waiting')
                ->get();

            $player1 = $match->first();
            $player1Avatar = ($player1 && $player1->user && !empty($player1->user->avatar))
                ? asset($player1->user->avatar)
                : 'https://www.svgrepo.com/show/384674/account-avatar-profile-user-11.svg';

            $alreadyJoined = $match->pluck('user_id')->contains($userId);

            $response[] = [
                'id' => $type->id,
                'title' => $type->title,
                'joining' => $type->joining,
                'winning' => $type->winning,
                'player1' => $player1Avatar,
                'player2' => asset('searching1.gif'),
                'status' => 'waiting',
                'created_at' => $type->created_at->toDateTimeString(),
                'joined' => $alreadyJoined,
                'redirect_url' =>  null,
            ];
        }

        foreach ($playingGames as $game) {
            $player1Avatar = ($game->player1 && !empty($game->player1->avatar)) ? asset($game->player1->avatar) : 'https://www.svgrepo.com/show/384674/account-avatar-profile-user-11.svg';
            $player2Avatar = ($game->player2 && !empty($game->player2->avatar)) ? asset($game->player2->avatar) : 'https://www.svgrepo.com/show/384674/account-avatar-profile-user-11.svg';

            $alreadyInGame = ($game->player_1 == $userId || $game->player_2 == $userId);
            $redirectUrl = null;

            if ($alreadyInGame) {
                $redirectUrl = $game->player_1 == $userId
                    ? $game->player_1_url
                    : $game->player_2_url;
            }

            $response[] = [
                'id' => $game->id,
                'title' => 'Match #' . $game->id,
                'joining' => $game->joining,
                'winning' => $game->winning,
                'player1' => $player1Avatar,
                'player2' => $player2Avatar,
                'status' => 'playing',
                'joined' => $alreadyInGame,
                'redirect_url' => $redirectUrl,
            ];
        }

        return response()->json($response);
    }

    public function games()
    {
        if (settings('snake_status') !== 'on') {
            return redirect()->back()->with('error', 'This Game is off');
        }
        $game_types = GameTypes::where('game', 'snake')->orderByRaw('CAST(joining AS DECIMAL(10,2)) ASC')->get();
        return view('user.snake', compact('game_types'));
    }
    public function gamesWaiting($id)
    {
        $game_type_id = $id;
        return view('user.snake_searching', compact('game_type_id'));
    }



    public function cancelMatch(Request $request)
    {
        $userId = user('id');
        $gameTypeId = $request->input('game_type_id');
        $gameType = GameTypes::findOrFail($gameTypeId);

        DB::transaction(function () use ($userId, $gameTypeId, $gameType) {
            $queue = MatchmakingQueue::where('user_id', $userId)
                ->where('game_type_id', $gameTypeId)
                ->lockForUpdate()
                ->first();

            if (!$queue) {
                abort(400, 'You have already cancelled this match or it has been processed.');
            }

            $queue->delete();

            $user = user();
            $user->increment('deposit_balance', $gameType->joining);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'action' => 'Canceled Snake and Ladders Match',
                'amount' => $gameType->joining,
                'created_at' => now(),
            ]);

            // ✅ Send OneSignal Notification
            if ($user->one_signal) {
                $formattedAmount = number_format($gameType->joining, 2);
                $time = now()->format('h:i A, M d, Y');

                $message = "Hi {$user->name},\n\n" .
                    "You have canceled your Snake and Ladders match.\n\n" .
                    "💵 Refunded Amount: BDT {$formattedAmount}\n🕒 Time: {$time}\n\n" .
                    "The amount has been credited back to your wallet.";

                OneSignalService::sendNotification(
                    'Match Cancelled & Refunded',
                    $message,
                    $user->one_signal
                );
            }
        });

        return response()->json(['status' => 'cancelled']);
    }


}
?>
