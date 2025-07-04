<!DOCTYPE html>
<html lang="en">

<head>
   @include('admin.parts.head', ['title' => 'Games - AppName'])
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
                  <div class="col-12 mx-auto">
                     <div class="card shadow-sm">
                        <div class="card-header bg-transparent">
                           <h4 class="mb-0"><i class="fas fa-gamepad me-2"></i>Games</h4>
                        </div>
                        <div class="card-body p-4">
                           @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                          {{ session('success') }}
                          <button type="button" class="btn-close" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                        </div>
                     @endif
                           @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                          {{ session('error') }}
                          <button type="button" class="btn-close" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                        </div>
                     @endif
                           <!-- Search and Filter Form -->
                           <form method="GET" action="{{ route('admin.games.index') }}" class="mb-4">
                              <div class="row g-3">
                                 <div class="col-md-9 mt-1">
                                    <div class="input-group">
                                       <input type="text" name="search" class="form-control"
                                          placeholder="Search by game type, player, status..."
                                          value="{{ request('search') }}">
                                       <button type="submit" class="btn btn-primary">
                                          <i class="fas fa-search"></i> Search
                                       </button>
                                    </div>
                                 </div>
                                 <div class="col-md-3 mt-1">
                                    <select name="status" class="form-control" onchange="this.form.submit()">
                                       <option value="" {{ request('status') == '' ? 'selected' : '' }}>All Statuses
                                       </option>
                                       <option value="playing" {{ request('status') == 'playing' ? 'selected' : '' }}>
                                          Playing</option>
                                       <option value="finished" {{ request('status') == 'finished' ? 'selected' : '' }}>
                                          Finished</option>
                                       <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>
                                          Cancelled
                                       </option>
                                    </select>
                                 </div>
                              </div>
                           </form>
                           <!-- Games Table -->
                           <div class="table-responsive">
                              <table class="table table-bordered table-hover">
                                 <thead>
                                    <tr>
                                       <th>ID</th>
                                       <th>API ID</th>
                                       <th>Game</th>
                                       <th>Player 1</th>
                                       <th>Player 2</th>
                                       <th>Joining</th>
                                       <th>Winning</th>
                                       <th>Status</th>
                                       <th>Winner</th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                    @forelse ($games as $game)
                                 <tr>
                                    <td>{{ $game->id }}</td>
                                    <td>{{ $game->api_game_id }}</td>
                                    <td>{{ ucwords($game->game)  }}</td>
                                    <td>
                                      {{ $game->player1 ? $game->player1->name : 'N/A' }}
                                      ({{ $game->player1 ? $game->player1->number : 'N/A' }})
                                    </td>
                                    <td>
                                      @if ($game->game_type == "Bot")
                                 Bot
                              @else
                                 {{ $game->player2 ? $game->player2->name : 'N/A' }}
                                 ({{ $game->player2 ? $game->player2->number : 'N/A' }})
                              @endif

                                    </td>
                                    <td>{{ number_format($game->joining, 2) }}
                                    </td>
                                    <td>{{ number_format($game->winning, 2) }}
                                    </td>
                                    <td>
                                      <span
                                        class="badge text-white bg-{{ $game->status == 'finished' ? 'success' : ($game->status == 'playing' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($game->status) }}
                                      </span>
                                    </td>
                                    <td>
                                      @if ($game->game_type == "Bot" && is_null($game->winner_id))
                                 Bot
                              @else
                                 {{ $game->winner ? $game->winner->name : 'N/A' }}
                                 ({{ $game->winner ? $game->winner->number : 'N/A' }})
                              @endif
                                    </td>
                                 </tr>
                           @empty
                              <tr>
                                 <td colspan="9" class="text-center">No
                                   games found.</td>
                              </tr>
                           @endforelse
                                 </tbody>
                              </table>
                           </div>
                           <!-- Pagination -->
                           <div class="d-flex justify-content-end">
                              {{ $games->appends(request()->query())->links() }}
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
</body>

</html>
