<!DOCTYPE html>
<html lang="en">


<head>
  @include('admin.parts.head', ['title' => 'Dashboard - AppName'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    integrity="sha512-4N4+/Wz20TePyyY+bH1tcB+xddPj5aPv1dupo6J4I3Nyd6iB0F3ECdwxCJgpOfWnNz+XsklQ1n8dw2wDRO2pGg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      @include('admin.parts.nav')
      <!-- Main Content -->
      <div class="main-content">
        <section class="section">


          <div class="row">

            {{-- Total Users --}}
            <div class="col-lg-4 col-md-6 col-sm-12 mb-0">
              <div class="card card-statistic-2 shadow-sm">
                <div class="card-icon shadow-primary bg-primary text-white">
                  <i class="fas fa-users"></i>
                </div>
                <div class="card-wrap text-end pe-3">
                  <div class="card-header">
                    <h4>Total Users</h4>
                  </div>
                  <div class="card-body">
                    {{ \App\Models\User::count() }}
                  </div>
                </div>
              </div>
            </div>

            {{-- Today's Signups --}}
            <div class="col-lg-4 col-md-6 col-sm-12 mb-0">
              <div class="card card-statistic-2 shadow-sm">
                <div class="card-icon shadow-info bg-info text-white">
                  <i class="fas fa-user-plus"></i>
                </div>
                <div class="card-wrap text-end pe-3">
                  <div class="card-header">
                    <h4>Today Signups</h4>
                  </div>
                  <div class="card-body">
                    {{ \App\Models\User::whereDate('created_at', \Carbon\Carbon::today())->count() }}
                  </div>
                </div>
              </div>
            </div>



            <div class="col-lg-4 col-md-6 col-sm-12 mb-0">
              <div class="card card-statistic-2 shadow-sm">
                <div class="card-icon shadow-success bg-success text-white">
                  <i class="fas fa-wallet"></i>
                </div>
                <div class="card-wrap text-end pe-3">
                  <div class="card-header">
                    <h4>Total Games Profit</h4>
                  </div>
                  <div class="card-body">
                    {{ number_format(($games->sum('joining') * 2) - $games->sum('winning'), 2) }} BDT
                  </div>
                </div>
              </div>
            </div>



<div class="col-lg-4 col-md-6 col-sm-12 mb-0">
  <div class="card card-statistic-2 shadow-sm">
    <div class="card-icon shadow-success bg-success text-white">
      <i class="fas fa-wallet"></i>
    </div>
    <div class="card-wrap text-end pe-3">
      <div class="card-header">
        <h4>Today's Profit</h4>
      </div>
      <div class="card-body">
        {{ number_format(($games->where('created_at', '>=', \Carbon\Carbon::today())->sum('joining') * 2) - $games->where('created_at', '>=', \Carbon\Carbon::today())->sum('winning'), 2) }} BDT
      </div>
    </div>
  </div>
</div>



            <div class="col-lg-4 col-md-6 col-sm-12 mb-0">
              <div class="card card-statistic-2 shadow-sm">
                <div class="card-icon shadow-primary bg-info text-white">
                  <i class="fas fa-wallet"></i>
                </div>
                <div class="card-wrap text-end pe-3">
                  <div class="card-header">
                    <h4>Total Users Balance</h4>
                  </div>
                  <div class="card-body">
                    {{ number_format($users->sum('deposit_balance') + $users->sum('winning_balance'), 2) }} BDT
                  </div>
                </div>
              </div>
            </div>


            <div class="col-lg-4 col-md-6 col-sm-12 mb-0">
              <div class="card card-statistic-2 shadow-sm">
                <div class="card-icon shadow-primary bg-info text-white">
                  <i class="fas fa-wallet"></i>
                </div>
                <div class="card-wrap text-end pe-3">
                  <div class="card-header">
                    <h4>Total Users Winning Balance</h4>
                  </div>
                  <div class="card-body">
                    {{ number_format($users->sum('winning_balance'), 2) }} BDT
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 mb-0">
              <div class="card card-statistic-2 shadow-sm">
                <div class="card-icon shadow-primary bg-info text-white">
                  <i class="fas fa-wallet"></i>
                </div>
                <div class="card-wrap text-end pe-3">
                  <div class="card-header">
                    <h4>Total Users Deposit Balance</h4>
                  </div>
                  <div class="card-body">
                    {{ number_format($users->sum('deposit_balance'), 2) }} BDT
                  </div>
                </div>
              </div>
            </div>



            <div class="col-lg-4 col-md-6 col-sm-12 mb-0">
              <div class="card card-statistic-2 shadow-sm">
                <div class="card-icon shadow-primary bg-success text-white">
                  <i class="fas fa-piggy-bank"></i>
                </div>
                <div class="card-wrap text-end pe-3">
                  <div class="card-header">
                    <h4>Total Deposits</h4>
                  </div>
                  <div class="card-body">
                    {{$deposits->count()}}
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 mb-0">
              <div class="card card-statistic-2 shadow-sm">
                <div class="card-icon shadow-primary bg-success text-white">
                  <i class="fas fa-piggy-bank"></i>
                </div>
                <div class="card-wrap text-end pe-3">
                  <div class="card-header">
                    <h4>Total Deposits Amount</h4>
                  </div>
                  <div class="card-body">
                    {{$deposits->sum('amount')}} BDT
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 mb-0">
              <div class="card card-statistic-2 shadow-sm">
                <div class="card-icon shadow-primary bg-success text-white">
                  <i class="fas fa-piggy-bank"></i>
                </div>
                <div class="card-wrap text-end pe-3">
                  <div class="card-header">
                    <h4>Total Withdraws</h4>
                  </div>
                  <div class="card-body">
                    {{$withdraws->count()}}
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 mb-0">
              <div class="card card-statistic-2 shadow-sm">
                <div class="card-icon shadow-primary bg-success text-white">
                  <i class="fas fa-piggy-bank"></i>
                </div>
                <div class="card-wrap text-end pe-3">
                  <div class="card-header">
                    <h4>Total Withdraw Amount</h4>
                  </div>
                  <div class="card-body">
                    {{$withdraws->sum('amount')}} BDT
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 mb-0">
              <div class="card card-statistic-2 shadow-sm">
                <div class="card-icon shadow-primary bg-purple text-white">
                  <i class="fas fa-gamepad"></i>
                </div>
                <div class="card-wrap text-end pe-3">
                  <div class="card-header">
                    <h4>Total Matches</h4>
                  </div>
                  <div class="card-body">
                    {{$games->count()}}
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 mb-0">
              <div class="card card-statistic-2 shadow-sm">
                <div class="card-icon shadow-primary bg-purple text-white">
                  <i class="fas fa-dice"></i>
                </div>
                <div class="card-wrap text-end pe-3">
                  <div class="card-header">
                    <h4>Ludo Matches</h4> <!-- Changed Title -->
                  </div>
                  <div class="card-body">
                    {{$games->where('game', 'ludo')->count()}}
                  </div>
                </div>
              </div>
            </div>


            <div class="col-lg-4 col-md-6 col-sm-12 mb-0">
              <div class="card card-statistic-2 shadow-sm">
                <div class="card-icon shadow-primary bg-warning text-white">
                  <i class="fas fa-dragon"></i> <!-- You can replace icon if you want -->
                </div>
                <div class="card-wrap text-end pe-3">
                  <div class="card-header">
                    <h4>Snake Games</h4>
                  </div>
                  <div class="card-body">
                    {{$games->where('game', 'snake')->count()}}
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 mb-0">
              <div class="card card-statistic-2 shadow-sm">
                <div class="card-icon shadow-primary bg-danger text-white">
                  <i class="fas fa-utensils"></i>
                </div>
                <div class="card-wrap text-end pe-3">
                  <div class="card-header">
                    <h4>Knife Games</h4>
                  </div>
                  <div class="card-body">
                    {{$games->where('game', 'knife')->count()}}
                  </div>
                </div>
              </div>
            </div>

          </div>


@php
  use App\Models\Game;
  use Carbon\Carbon;

  $today = Carbon::today();

  // Today's Stats for Snake
  $todaySnakeMatches = Game::where('game_type', 'Snake')->whereDate('created_at', $today)->whereNot('status', 'canceled')->count();
  $todaySnakeWins = Game::where('game_type', 'Snake')->where('is_bot_winner', 'Yes')->whereDate('created_at', $today)->whereNot('status', 'canceled')->count();
  $todaySnakeLosses = $todaySnakeMatches - $todaySnakeWins;
  $todaySnakeWinsAmount = Game::where('game_type', 'Snake')->where('is_bot_winner', 'Yes')->whereDate('created_at', $today)->whereNot('status', 'canceled')->sum('joining');
  $todaySnakeLossesAmount = Game::where('game_type', 'Snake')->where('is_bot_winner', 'No')->whereDate('created_at', $today)->whereNot('status', 'canceled')->sum('winning') -
                             Game::where('game_type', 'Snake')->where('is_bot_winner', 'No')->whereDate('created_at', $today)->whereNot('status', 'canceled')->sum('joining');
  $todaySnakeProfit = $todaySnakeWinsAmount - $todaySnakeLossesAmount;

  $todaySnakeWinsAmountFormatted = number_format($todaySnakeWinsAmount, 2);
  $todaySnakeLossesAmountFormatted = number_format($todaySnakeLossesAmount, 2);
  $todaySnakeProfitFormatted = number_format($todaySnakeProfit, 2);

  // Lifetime Stats
  $lifetimeSnakeMatches = Game::where('game_type', 'Snake')->whereNot('status', 'canceled')->count();
  $lifetimeSnakeWins = Game::where('game_type', 'Snake')->whereNot('status', 'canceled')->where('is_bot_winner', 'Yes')->count();
  $lifetimeSnakeLosses = $lifetimeSnakeMatches - $lifetimeSnakeWins;
  $lifetimeSnakeWinsAmount = Game::where('game_type', 'Snake')->whereNot('status', 'canceled')->where('is_bot_winner', 'Yes')->sum('joining');
  $lifetimeSnakeLossesAmount = Game::where('game_type', 'Snake')->whereNot('status', 'canceled')->where('is_bot_winner', 'No')->sum('winning') -
                                Game::where('game_type', 'Snake')->where('is_bot_winner', 'No')->whereNot('status', 'canceled')->sum('joining');
  $lifetimeSnakeProfit = $lifetimeSnakeWinsAmount - $lifetimeSnakeLossesAmount;

  $lifetimeSnakeWinsAmountFormatted = number_format($lifetimeSnakeWinsAmount, 2);
  $lifetimeSnakeLossesAmountFormatted = number_format($lifetimeSnakeLossesAmount, 2);
  $lifetimeSnakeProfitFormatted = number_format($lifetimeSnakeProfit, 2);
@endphp

<div class="row">
  <div class="col-12">
    <h2>Snake Bot Details</h2>
  </div>

  <div class="col-12">
    <h4>Today's Snake Bot Statistics ({{ $today->format('M d, Y') }})</h4>
  </div>

  <div class="col-lg-4 col-md-6 col-sm-12">
    <div class="card card-statistic-2 shadow-sm">
      <div class="card-icon shadow-primary bg-primary text-white"><i class="fas fa-gamepad"></i></div>
      <div class="card-wrap text-end pe-3">
        <div class="card-header"><h4>Total Snake Matches Played</h4></div>
        <div class="card-body">{{ $todaySnakeMatches }} Matches</div>
      </div>
    </div>
  </div>

  <div class="col-lg-4 col-md-6 col-sm-12">
    <div class="card card-statistic-2 shadow-sm">
      <div class="card-icon shadow-success bg-success text-white"><i class="fas fa-trophy"></i></div>
      <div class="card-wrap text-end pe-3">
        <div class="card-header"><h4>Total Snake Wins Amount</h4></div>
        <div class="card-body">{{ $todaySnakeWinsAmountFormatted }} BDT</div>
      </div>
    </div>
  </div>

  <div class="col-lg-4 col-md-6 col-sm-12">
    <div class="card card-statistic-2 shadow-sm">
      <div class="card-icon shadow-danger bg-danger text-white"><i class="fas fa-times-circle"></i></div>
      <div class="card-wrap text-end pe-3">
        <div class="card-header"><h4>Total Snake Losses Amount</h4></div>
        <div class="card-body">{{ $todaySnakeLossesAmountFormatted }} BDT</div>
      </div>
    </div>
  </div>

  <div class="col-lg-4 col-md-6 col-sm-12">
    <div class="card card-statistic-2 shadow-sm">
      <div class="card-icon shadow-info bg-info text-white"><i class="fas fa-wallet"></i></div>
      <div class="card-wrap text-end pe-3">
        <div class="card-header"><h4>Total Snake Profit</h4></div>
        <div class="card-body">{{ $todaySnakeProfitFormatted }} BDT</div>
      </div>
    </div>
  </div>

  <div class="col-12 mt-2">
    <h4>Lifetime Snake Bot Statistics</h4>
  </div>

  <div class="col-lg-4 col-md-6 col-sm-12">
    <div class="card card-statistic-2 shadow-sm">
      <div class="card-icon shadow-primary bg-primary text-white"><i class="fas fa-gamepad"></i></div>
      <div class="card-wrap text-end pe-3">
        <div class="card-header"><h4>Total Snake Matches Played</h4></div>
        <div class="card-body">{{ $lifetimeSnakeMatches }} Matches</div>
      </div>
    </div>
  </div>

  <div class="col-lg-4 col-md-6 col-sm-12">
    <div class="card card-statistic-2 shadow-sm">
      <div class="card-icon shadow-success bg-success text-white"><i class="fas fa-trophy"></i></div>
      <div class="card-wrap text-end pe-3">
        <div class="card-header"><h4>Total Snake Wins Amount</h4></div>
        <div class="card-body">{{ $lifetimeSnakeWinsAmountFormatted }} BDT</div>
      </div>
    </div>
  </div>

  <div class="col-lg-4 col-md-6 col-sm-12">
    <div class="card card-statistic-2 shadow-sm">
      <div class="card-icon shadow-danger bg-danger text-white"><i class="fas fa-times-circle"></i></div>
      <div class="card-wrap text-end pe-3">
        <div class="card-header"><h4>Total Snake Losses Amount</h4></div>
        <div class="card-body">{{ $lifetimeSnakeLossesAmountFormatted }} BDT</div>
      </div>
    </div>
  </div>

  <div class="col-lg-4 col-md-6 col-sm-12">
    <div class="card card-statistic-2 shadow-sm">
      <div class="card-icon shadow-info bg-info text-white"><i class="fas fa-wallet"></i></div>
      <div class="card-wrap text-end pe-3">
        <div class="card-header"><h4>Total Snake Profit</h4></div>
        <div class="card-body">{{ $lifetimeSnakeProfitFormatted }} BDT</div>
      </div>
    </div>
  </div>
</div>


          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4>My Games History</h4>
                </div>
                <div class="card-body">
                  <div class="summary">
                    <div class="summary-chart active" data-tab-group="summary-tab" id="summary-chart">
                      <div id="chart3" class="chartsh"></div>
                    </div>
                    <div data-tab-group="summary-tab" id="summary-text">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>


        </section>

      </div>
      @include('admin.parts.footer')
    </div>
  </div>
  @include('admin.parts.scripts')
  <script>
    var chartLabels = [];
    var chartSeries = [];

    @php
    // Collect all unique dates in the range
    $allDates = collect([]);
    foreach ($chartData as $status => $entries) {
      $allDates = $allDates->merge($entries->pluck('date'));
    }
    $allDates = $allDates->unique()->sort()->values();

    // Map status -> daily count across those dates
    $series = [];
    foreach ($chartData as $status => $entries) {
      $data = [];
      foreach ($allDates as $date) {
      $dayData = $entries->firstWhere('date', $date);
      $data[] = $dayData ? $dayData->total : 0;
      }
      $series[] = [
      'name' => $status,
      'data' => $data
      ];
    }
    @endphp

    chartLabels = {!! json_encode($allDates->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))->values()) !!};
    chartSeries = {!! json_encode($series) !!};
  </script>

  <script>
    $(function () {
      chart3();
    });

    function chart3() {
      var options = {
        chart: {
          height: 250,
          type: 'line',
          zoom: { enabled: false },
          toolbar: { show: false }
        },
        dataLabels: { enabled: false },
        stroke: {
          width: 3,
          curve: 'smooth'
        },
        series: chartSeries,
        markers: { size: 4 },
        xaxis: {
          categories: chartLabels,
          labels: { style: { colors: "#9aa0ac" } }
        },
        yaxis: {
          labels: { style: { color: "#9aa0ac" } }
        },
        grid: {
          borderColor: '#f1f1f1',
        },
        tooltip: {}
      };

      var chart = new ApexCharts(document.querySelector("#chart3"), options);
      chart.render();
    }
  </script>


</body>

</html>
