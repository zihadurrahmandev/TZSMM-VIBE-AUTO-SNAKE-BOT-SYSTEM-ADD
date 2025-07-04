<!DOCTYPE html>
<html lang="en">

<head>
  @include('admin.parts.head', ['title' => 'Snake Settings - AppName'])
  <style>
    .nav-tabs .nav-link {
      border: none;
      border-radius: 0;
      padding: 10px 20px;
      color: #495057;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .nav-tabs .nav-link:hover {
      background-color: #f8f9fa;
      color: #007bff;
    }

    .nav-tabs .nav-link.active {
      background-color: #007bff;
      color: white !important;
      border-bottom: 3px solid #0056b3;
    }

    .collapse-card .card-header {
      cursor: pointer;
      background-color: #f1f3f5;
    }

    .collapse-card .card-header:hover {
      background-color: #e9ecef;
    }
  </style>
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      @include('admin.parts.nav')
      <div class="main-content">
        <section class="section">
          <div class="section-body">
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h3 class="mt-3">Snake Settings</h3>
                  </div>
                  <div class="card-body pt-0">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs" id="snakeSettingsTabs" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link active" id="settings-tab" data-bs-toggle="tab" href="#settings" role="tab"
                          aria-controls="settings" aria-selected="true">Snake Settings</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" id="game-types-tab" data-bs-toggle="tab" href="#game-types" role="tab"
                          aria-controls="game-types" aria-selected="false">Game Types</a>
                      </li>
                    </ul>
                    <div class="tab-content" id="snakeSettingsTabsContent">
                      <!-- Snake Settings Tab -->
                      <div class="tab-pane fade show active" id="settings" role="tabpanel"
                        aria-labelledby="settings-tab">
                        <div class="collapse-card mt-3">
                          <div class="card">
                            <div class="card-header" data-bs-toggle="collapse" data-bs-target="#snakeSettingsCollapse"
                              aria-expanded="true">
                              <h5 class="mb-0">Snake Settings</h5>
                            </div>
                            <div id="snakeSettingsCollapse" class="collapse show">
                              <div class="card-body">
                                <form method="POST" action="{{ route('admin.snake-settings.update') }}"
                                  enctype="multipart/form-data">
                                  @csrf
                                  <div class="row">
                                      
                                    <div class="col-md-4 form-group">
                                      <label for="snake_status">Snake Status</label>
                                      <select class="form-control form-control-sm" id="snake_status" name="snake_status"
                                        required>
                                        <option value="on" {{ settings('snake_status') == 'on' ? 'selected' : '' }}>On
                                        </option>
                                        <option value="off" {{ settings('snake_status') == 'off' ? 'selected' : '' }}>Off
                                        </option>
                                      </select>
                                    </div>

                                    <div class="col-md-4 form-group">
                                      <label for="snake_logo">Snake Logo</label>
                                      <input type="file" class="form-control-file form-control-sm" id="snake_logo"
                                        name="snake_logo" accept="image/*">
                                      @if(settings('snake_logo'))
                      <div class="mt-2">
                      <img src="{{ asset(settings('snake_logo')) }}" alt="Snake Logo"
                        style="max-width: 100px; max-height: 100px;">
                      </div>
                    @endif
                                    </div>

                                    <div class="col-md-4 form-group">
                                      <label for="snake_subtitle">Snake Subtitle</label>
                                      <input type="text" class="form-control form-control-sm" id="snake_subtitle"
                                        name="snake_subtitle"
                                        value="{{ old('snake_subtitle', settings('snake_subtitle') ?? '') }}"
                                        placeholder="Enter Snake subtitle">
                                    </div>

                                    <div class="col-md-4 form-group mt-3">
                                      <label for="snake_minimum">Minimum Entry Fee (৳)</label>
                                      <input type="number" class="form-control form-control-sm" id="snake_minimum"
                                        name="snake_minimum" min="1"
                                        value="{{ old('snake_minimum', settings('snake_minimum') ?? 30) }}"
                                        placeholder="e.g. 30">
                                    </div>

                                    <div class="col-md-4 form-group mt-3">
                                      <label for="snake_winning">Winning Multiplier</label>
                                      <input type="number" step="0.01" class="form-control form-control-sm"
                                        id="snake_winning" name="snake_winning"
                                        value="{{ old('snake_winning', settings('snake_winning') ?? 1.8) }}"
                                        placeholder="e.g. 1.8">
                                    </div>
                                    
                                    
                                    <div class="col-md-4 form-group">
                                      <label for="snake_bot_status">Snake Bot Status</label>
                                      <select class="form-control form-control-sm" id="snake_bot_status"
                                        name="snake_bot_status" required>
                                        <option value="on" {{ settings('snake_bot_status') == 'on' ? 'selected' : '' }}>On</option>
                                        <option value="off" {{ settings('snake_bot_status') == 'off' ? 'selected' : '' }}>Off</option>
                                      </select>
                                    </div>
                                    
                                    <div class="col-md-4 form-group">
                                      <label for="snake_bot_winning">Snake Bot Winning %</label>
                                      <input type="number" step="0.01" class="form-control form-control-sm"
                                        id="snake_bot_winning" name="snake_bot_winning" min="1" max="100" required
                                        value="{{ old('snake_bot_winning', settings('snake_bot_winning') ?? '') }}"
                                        placeholder="Snake bot winning % e.g. 70">
                                    </div>



                                  </div>

                                  <button type="submit" class="btn btn-primary btn-sm mt-3">Save Settings</button>
                                </form>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Game Types Tab -->
                      <div class="tab-pane fade" id="game-types" role="tabpanel" aria-labelledby="game-types-tab">
                        <div class="mt-3">
                          <button type="button" class="btn btn-success btn-sm mb-3" data-bs-toggle="modal"
                            data-bs-target="#addGameTypeModal">Add New Game Type</button>
                        </div>
                        <div class="table-responsive">
                          <table class="table table-striped">
                            <thead>
                              <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Joining Fee</th>
                                <th>Player Count</th>
                                <th>Winning Amount</th>
                                <th>Game</th>
                                <th>Actions</th>
                              </tr>
                            </thead>
                            <tbody>
                              @foreach($game_types as $game_type)
                  <tr>
                  <td>{{ $game_type->id }}</td>
                  <td>{{ $game_type->title }}</td>
                  <td>{{ number_format($game_type->joining, 2) }}</td>
                  <td>{{ $game_type->player }}</td>
                  <td>{{ number_format($game_type->winning, 2) }}</td>
                  <td>{{ $game_type->game }}</td>
                  <td>
                    <button type="button" class="btn btn-sm btn-primary mb-1" data-bs-toggle="modal"
                    data-bs-target="#editGameTypeModal{{ $game_type->id }}">Edit</button>
                    <form action="{{ route('admin.game-types.destroy', $game_type->id) }}" method="POST"
                    style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger mb-1"
                      onclick="return confirm('Are you sure you want to delete this game type?')">Delete</button>
                    </form>
                  </td>
                  </tr>
                @endforeach
                            </tbody>
                          </table>
                        </div>
                      </div>
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
  <!-- Add Game Type Modal -->
  <div class="modal fade" id="addGameTypeModal" tabindex="-1" role="dialog" aria-labelledby="addGameTypeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addGameTypeModalLabel">Add New Game Type</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('admin.game-types.store') }}">
          @csrf
          <div class="modal-body">
            <div class="form-group">
              <label for="title">Title</label>
              <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required>
            </div>
            <div class="form-group">
              <label for="joining">Joining Fee</label>
              <input type="number" step="0.01" class="form-control" id="joining" name="joining"
                value="{{ old('joining') }}" required>
            </div>
            <div class="form-group d-none">
              <label for="player">Player Count</label>
              <input type="number" class="form-control" id="player" name="player" value="2" required>
            </div>
            <div class="form-group">
              <label for="winning">Winning Amount</label>
              <input type="number" step="0.01" class="form-control" id="winning" name="winning"
                value="{{ old('winning') }}" required>
            </div>
            <input type="hidden" name="game" value="snake">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">Add Game Type</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- Edit Game Type Modals -->
  @foreach ($game_types as $game_type)
    <div class="modal fade" id="editGameTypeModal{{ $game_type->id }}" tabindex="-1" role="dialog"
    aria-labelledby="editGameTypeModalLabel{{ $game_type->id }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editGameTypeModalLabel{{ $game_type->id }}">Edit Game Type:
        {{ $game_type->title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('admin.game-types.update', $game_type->id) }}">
        @csrf
        @method('PUT')
        <div class="modal-body">
        <div class="form-group">
          <label for="title{{ $game_type->id }}">Title</label>
          <input type="text" class="form-control" id="title{{ $game_type->id }}" name="title"
          value="{{ $game_type->title }}" required>
        </div>
        <div class="form-group">
          <label for="joining{{ $game_type->id }}">Joining Fee</label>
          <input type="number" step="0.01" class="form-control" id="joining{{ $game_type->id }}" name="joining"
          value="{{ $game_type->joining }}" required>
        </div>
        <div class="form-group d-none">
          <label for="player{{ $game_type->id }}">Player Count</label>
          <input type="number" class="form-control" id="player{{ $game_type->id }}" name="player"
          value="{{ $game_type->player }}" required>
        </div>
        <div class="form-group">
          <label for="winning{{ $game_type->id }}">Winning Amount</label>
          <input type="number" step="0.01" class="form-control" id="winning{{ $game_type->id }}" name="winning"
          value="{{ $game_type->winning }}" required>
        </div>
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
      </div>
    </div>
    </div>
  @endforeach
  @include('admin.parts.scripts')
</body>

</html>
