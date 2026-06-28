<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Todo</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/common.css') }}">
  @yield('css')
</head>

<body>
  <header class="header">
    <div class="header__inner">
      <div class="header-utilities">
        <a class="header__logo" href="/">
          Todo
        </a>
        <nav>
          <ul class="header-nav">
            <li class="header-nav__item">
              <a class="header-nav__link" href="/categories">カテゴリ一覧</a>
            </li>
            @auth
            <li class="header-nav__item">
              <div class="header-user-menu js-user-menu">
                <button class="header-user-menu__button js-user-menu-button" type="button" aria-expanded="false">
                  {{ Auth::user()->name }} ▼
                </button>
                <div class="header-user-menu__dropdown js-user-menu-dropdown" aria-hidden="true">
                  <form class="header-user-menu__form" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="header-user-menu__logout" type="submit">ログアウト</button>
                  </form>
                </div>
              </div>
            </li>
            @endauth
          </ul>
        </nav>
      </div>
    </div>
  </header>

  <main>
    @yield('content')
  </main>
  @yield('scripts')
  @auth
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const userMenu = document.querySelector('.js-user-menu');

      if (!userMenu) {
        return;
      }

      const menuButton = userMenu.querySelector('.js-user-menu-button');
      const dropdown = userMenu.querySelector('.js-user-menu-dropdown');

      const closeMenu = () => {
        userMenu.classList.remove('header-user-menu--open');
        menuButton.setAttribute('aria-expanded', 'false');
        dropdown.setAttribute('aria-hidden', 'true');
      };

      menuButton.addEventListener('click', (event) => {
        event.stopPropagation();
        const isOpen = userMenu.classList.toggle('header-user-menu--open');
        menuButton.setAttribute('aria-expanded', String(isOpen));
        dropdown.setAttribute('aria-hidden', String(!isOpen));
      });

      dropdown.addEventListener('click', (event) => {
        event.stopPropagation();
      });

      document.addEventListener('click', closeMenu);
      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
          closeMenu();
        }
      });
    });
  </script>
  @endauth
</body>

</html>
