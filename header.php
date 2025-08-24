<div class="header">
    <h1 class="game-title">Exodus Genesis</h1>
    <p class="subtitle">Space Exploration 1.01</p>
    <style>
        .hamburger {
            width: 38px;
            height: 38px;
            display: inline-block;
            cursor: pointer;
            position: relative;
            margin-top: 24px;
        }
        .hamburger span {
            display: block;
            height: 5px;
            width: 100%;
            background: #00d4ff;
            margin: 7px 0;
            border-radius: 3px;
            transition: 0.4s;
        }
        .hamburger.active span:nth-child(1) {
            transform: translateY(12px) rotate(45deg);
        }
        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }
        .hamburger.active span:nth-child(3) {
            transform: translateY(-12px) rotate(-45deg);
        }
        .menu-drawer {
            position: absolute;
            top: 70px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(30,30,40,0.97);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.25);
            padding: 24px 0;
            min-width: 220px;
            z-index: 999;
            display: none;
            animation: fadeInMenu 0.4s;
        }
        @keyframes fadeInMenu {
            from { opacity: 0; transform: translateY(-20px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .menu-drawer ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .menu-drawer li {
            margin: 18px 0;
        }
        .menu-drawer a {
            color: #fff;
            font-size: 1.2rem;
            font-weight: bold;
            text-decoration: none;
            transition: color 0.2s;
        }
        .menu-drawer a:hover {
            color: #00d4ff;
        }
    </style>
    <div style="position:absolute; top:24px; left:32px; z-index:1000;">
        <div class="hamburger" id="hamburger-menu">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <nav class="menu-drawer" id="menu-drawer">
            <ul>
                <li><a href="#">Resources</a></li>
                <li><a href="#">Fleet</a></li>
                <li><a href="#">Planets</a></li>
                <li><a href="#">Account</a></li>
            </ul>
        </nav>
    </div>
    <script>
        const hamburger = document.getElementById('hamburger-menu');
        const drawer = document.getElementById('menu-drawer');
        hamburger.onclick = function() {
            hamburger.classList.toggle('active');
            if (drawer.style.display === 'block') {
                drawer.style.display = 'none';
            } else {
                drawer.style.display = 'block';
            }
        };
        // Optional: close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!hamburger.contains(e.target) && !drawer.contains(e.target)) {
                drawer.style.display = 'none';
                hamburger.classList.remove('active');
            }
        });
    </script>
</div>
