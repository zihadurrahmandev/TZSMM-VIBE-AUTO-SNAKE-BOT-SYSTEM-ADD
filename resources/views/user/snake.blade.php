<!DOCTYPE html>
<html lang="en">
@include('parts.head', ['title' => 'Play ' . settings('snake_subtitle') . ' - AppName'])

<style>
  .header {
    background: linear-gradient(135deg, #2A1B6D, #1A143D);
    padding: 8px 12px;
    display: flex;
    align-items: center;
    justify-content: left;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 50;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .header .back-btn {
    background: none;
    border: none;
    color: white;
    font-size: 1.2rem;
    margin-right: 10px;
  }

  .header .game-info {
    display: flex;
    gap: 8px;
    align-items: center;
  }

  .header .game-info img {
    width: 28px;
    height: 28px;
    border-radius: 4px;
  }

  .header .game-info .title {
    font-size: 0.9rem;
    font-weight: 700;
    color: white;
  }

  .header .game-info .subtitle {
    font-size: 0.7rem;
    color: #D0D0E0;
  }

  .tournaments-section {
    padding: 50px 8px 60px;
    margin-top: 40px;
  }

  .start-btn {
    background: linear-gradient(135deg, #00c6ff, #0072ff);
    color: white;
    font-weight: 700;
    font-size: 0.95rem;
    padding: 10px 15px;
    border-radius: 30px;
    text-align: center;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 4px 15px rgba(0, 114, 255, 0.3);
  }

  .start-btn .coin {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    width: 28px;
    height: 28px;
    font-size: 1rem;
  }

  .joining-input {
    background: #14102D;
    border: 2px solid transparent;
    padding: 8px 44px 8px 16px;
    font-size: 1.1rem;
    border-radius: 50px;
    box-shadow: inset 0 0 10px rgba(0, 255, 255, 0.1);
  }

  .joining-input:focus {
    border-color: #00e0ff;
    background: #1b1442;
    box-shadow: 0 0 10px #00e0ff, inset 0 0 10px rgba(0, 255, 255, 0.2);
  }

  .match-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  }

  .progress {
    background-color: #f0f0f0;
    border-radius: 8px;
    overflow: hidden;
  }

  .progress-bar.bg-warning {
    background: linear-gradient(135deg, #ffcc00, #ffaa00);
  }

  .progress-bar.bg-danger {
    background: linear-gradient(135deg, #ff4e4e, #d40000);
  }
</style>

<body>
  <div class="header">
    <button onclick="window.location.href='{{ route('dashboard') }}'" class="back-btn"><i
        class="fas fa-arrow-left"></i></button>
    <div class="game-info">
      <img src="{{ asset(settings('snake_logo')) }}" alt="Snake Icon">
      <div>
        <div class="title">{{ settings('snake_subtitle') }}</div>
        <div class="subtitle">Battle & Tournament</div>
      </div>
    </div>
  </div>

  <main class="tournaments-section container text-white">
    <div class="mb-3 text-center" style="justify-items: anchor-center;">
      <button class="start-btn mt-2" data-bs-toggle="modal" data-bs-target="#createSnakeModal">
        <i class="fas fa-plus-circle"></i> Create Match
      </button>
    </div>
    <div class="row gy-4" id="snakeTypes"></div>
  </main>

  <!-- Create Game Modal -->
  <div class="modal fade" id="createSnakeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <form method="POST" action="{{ route('games.snake.create') }}" class="modal-content"
        style="background: linear-gradient(135deg, #2A1B6D, #1A143D); border-radius: 16px; box-shadow: 0 0 25px rgba(0, 153, 255, 0.3);">
        @csrf
        <div class="modal-header border-0 px-4 pt-4">
          <h5 class="modal-title text-white fw-bold">🐍 Create Snake Match</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body px-4">
          <label for="joiningAmountSnake" class="form-label text-white mb-2">💰 Entry Fee (৳)</label>
          <div class="position-relative">
            <input type="number" name="joining" id="joiningAmountSnake" class="form-control text-white joining-input"
              placeholder="Minimum ৳{{ settings('snake_minimum') }} to start" step="1" required
              min="{{ settings('snake_minimum') ?? 30 }}">
            <i class="fas fa-coins position-absolute"
              style="right: 16px; top: 50%; transform: translateY(-50%); color: #00e0ff;"></i>
          </div>

          <div id="snakeWinningDisplay" class="text-info mt-3 fw-semibold" style="font-size: 0.95rem; display: none;">
            🏆 <span id="snakeWinningAmount">৳ 0</span> will be the prize!
          </div>

          <div id="snakeErrorDisplay" class="text-danger mt-2 fw-semibold" style="display: none;">
            ❌ Minimum joining amount is ৳{{ settings('snake_minimum') ?? 30 }}
          </div>
        </div>

        <div class="modal-footer border-0 px-4 pb-4">
          <button type="submit" class="start-btn w-100 py-2"
            onclick="this.disabled=true; this.innerHTML='<span class=\'coin\'><i class=\'fas fa-spinner fa-spin\'></i></span> Creating...'; this.form.submit();">
            <span class="coin"><i class="fas fa-plus-circle"></i></span>
            Create & Join
          </button>
        </div>
      </form>
    </div>
  </div>

  @include('parts.script')


  <script>
    const joiningSnake = document.getElementById('joiningAmountSnake');
    const winningDisplaySnake = document.getElementById('snakeWinningDisplay');
    const winningAmountSnake = document.getElementById('snakeWinningAmount');
    const errorDisplaySnake = document.getElementById('snakeErrorDisplay');
    const snakeMinimum = {{ settings('snake_minimum') ?? 30 }};
    const snakeMultiplier = {{ settings('snake_winning') ?? 1.8 }};

    joiningSnake.addEventListener('input', function () {
      this.value = this.value.replace(/[^\d]/g, '');
      const value = parseFloat(this.value);
      if (isNaN(value) || value < snakeMinimum) {
        winningDisplaySnake.style.display = 'none';
        errorDisplaySnake.style.display = 'block';
      } else {
        const win = (value * snakeMultiplier).toFixed(0);
        winningAmountSnake.innerText = `৳ ${win}`;
        winningDisplaySnake.style.display = 'block';
        errorDisplaySnake.style.display = 'none';
      }
    });

  </script>

  <script>
    const snakeTimers = {};
    const prevSnakeData = {};

    function loadSnakeTypes() {
      fetch('{{ route('snake.queue.status') }}')
        .then(res => res.json())
        .then(data => {
          const container = document.getElementById('snakeTypes');
          const now = Date.now();
          const currentIds = new Set(data.map(item => item.id));

          Object.keys(prevSnakeData).forEach(id => {
            if (!currentIds.has(Number(id))) {
              const card = document.getElementById(`match-${id}`);
              if (card) card.remove();
              if (snakeTimers[id]) clearInterval(snakeTimers[id]);
              delete prevSnakeData[id];
            }
          });

          data.forEach(item => {
            const cardId = `match-${item.id}`;
            const createdAt = item.created_at ? new Date(item.created_at).getTime() : now;
            const diffSeconds = Math.floor((now - createdAt) / 1000);
            const remainingSeconds = Math.max(0, 300 - diffSeconds);

            if (prevSnakeData[item.id] && JSON.stringify(prevSnakeData[item.id]) === JSON.stringify(item)) return;
            prevSnakeData[item.id] = item;

            const progress = item.status === 'playing' ? 100 : 50;
            const statusColor = item.status === 'playing' ? 'danger' : 'primary';
            const progressClass = item.status === 'playing' ? 'bg-danger' : 'bg-warning';
            const timerId = `timer-${item.id}`;

            const timerDisplay = item.status === 'waiting'
              ? `<span id="${timerId}" class="badge bg-info text-white w-100 fw-semibold py-1 rounded-pill mt-2">⏳ Time Left: ${formatTime(remainingSeconds)}</span>` : '';

            const actionButton = item.joined && item.redirect_url
              ? `<a href="${item.redirect_url}" class="start-btn mt-2 d-block text-center">
                <span class="coin"><i class="fas fa-sign-in-alt"></i></span>
                Rejoin Match
              </a>`
              : item.status === 'waiting' && !item.joined && !item.redirect_url
                ? `<a href="/games/ludo/join/${item.id}" class="start-btn mt-2 d-block text-center">
                  <span class="coin"><i class="fas fa-play"></i></span>
                  Join Match — ৳ ${item.joining}
                </a>`
                : '';

            const content = `
            <div class="match-card text-dark rounded shadow p-3 mb-3 position-relative" id="${cardId}">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div><div class="fw-bold text-success">${item.title}</div></div>
                <span class="badge bg-danger text-white">LIVE</span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <div><div class="text-muted small">TOTAL PRIZE</div><div class="fw-bold">৳ ${item.winning}</div></div>
                <div class="text-end"><div class="text-muted small">ENTRY FEE</div><div class="fw-bold text-danger">৳ ${item.joining}</div></div>
              </div>
              <div class="progress mb-2" style="height: 8px;">
                <div class="progress-bar ${progressClass}" role="progressbar" style="width: ${progress}%;"></div>
              </div>
              <div class="text-center fw-bold small mb-2 text-${statusColor}">${item.status === 'playing' ? 'Match Playing' : 'Waiting for 1 More'}</div>
              <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                <img src="${item.player1}" class="rounded-circle" style="width: 40px; height: 40px;">
                <strong class="text-danger">VS</strong>
                <img src="${item.player2}" class="rounded-circle" style="width: 40px; height: 40px;">
              </div>
              ${timerDisplay}
              ${actionButton}
            </div>`;

            const existing = document.getElementById(cardId);
            if (existing) {
              existing.outerHTML = content;
            } else {
              const wrapper = document.createElement('div');
              wrapper.className = 'col-12 col-sm-6 col-md-4 col-lg-3';
              wrapper.innerHTML = content;
              container.appendChild(wrapper);
            }

            if (item.status === 'waiting') {
              if (snakeTimers[item.id]) clearInterval(snakeTimers[item.id]);
              const timerEl = () => document.getElementById(timerId);
              snakeTimers[item.id] = setInterval(() => {
                const now = Date.now();
                const left = Math.max(0, 300 - Math.floor((now - createdAt) / 1000));
                const el = timerEl();
                if (!el) return clearInterval(snakeTimers[item.id]);
                if (left <= 0) {
                  el.outerHTML = `<span class="badge bg-secondary w-100 fw-semibold py-1 rounded-pill mt-2">⏱ Started</span>`;
                  clearInterval(snakeTimers[item.id]);
                } else {
                  el.textContent = `⏳ Time Left: ${formatTime(left)}`;
                }
              }, 1000);
            }
          });
        });
    }

    function formatTime(seconds) {
      const m = String(Math.floor(seconds / 60)).padStart(2, '0');
      const s = String(seconds % 60).padStart(2, '0');
      return `${m}:${s}`;
    }

    loadSnakeTypes();
    setInterval(loadSnakeTypes, 3000);
  </script>
</body>

</html>
