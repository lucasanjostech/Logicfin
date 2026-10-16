<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Acesso ao sistema de contas a pagar LogicFin">
    <title>Login | LogicFin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        :root {
            --ink: #14213d;
            --muted: #74819a;
            --line: #dce4ef;
            --brand: #1167b1;
            --brand-dark: #0a447c;
            --accent: #26b99a;
            --panel: #f7f9fc;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            color: var(--ink);
            font-family: "DM Sans", sans-serif;
            background: #edf2f8;
        }

        .login-shell {
            min-height: 100vh;
            padding: clamp(.75rem, 2vw, 1.5rem);
        }

        .login-card {
            min-height: calc(100vh - clamp(1.5rem, 4vw, 3rem));
            overflow: hidden;
            border: 0;
            border-radius: 28px;
            background: var(--panel);
            box-shadow: 0 24px 70px rgba(25, 47, 78, .14);
        }

        .visual-panel {
            position: relative;
            min-height: 560px;
            isolation: isolate;
            color: #fff;
            background:
                linear-gradient(140deg, rgba(3, 26, 51, .94), rgba(7, 51, 88, .78)),
                radial-gradient(circle at 78% 18%, rgba(38, 185, 154, .38), transparent 34%),
                #07345a;
        }

        .visual-panel::before,
        .visual-panel::after {
            position: absolute;
            content: "";
            z-index: -1;
            border-radius: 999px;
        }

        .visual-panel::before {
            width: 520px;
            height: 520px;
            top: -260px;
            right: -170px;
            border: 1px solid rgba(255, 255, 255, .12);
            box-shadow: 0 0 0 70px rgba(255, 255, 255, .025), 0 0 0 140px rgba(255, 255, 255, .018);
        }

        .visual-panel::after {
            width: 430px;
            height: 430px;
            right: -170px;
            bottom: -230px;
            background: rgba(38, 185, 154, .08);
        }

        .brand-mark {
            width: 52px;
            height: 52px;
            display: inline-grid;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 16px;
            color: #fff;
            font-size: 1.35rem;
            background: linear-gradient(135deg, var(--brand), #1d88d0);
            box-shadow: 0 12px 28px rgba(17, 103, 177, .25);
        }

        .brand-name,
        h1,
        .hero-title { font-family: "Manrope", sans-serif; }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            padding: .55rem .85rem;
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: 999px;
            color: #d6e9f6;
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, .08);
            backdrop-filter: blur(10px);
        }

        .hero-title {
            max-width: 680px;
            margin: 1.4rem 0 1rem;
            font-size: clamp(2.4rem, 5.1vw, 5rem);
            font-weight: 800;
            line-height: 1.02;
            letter-spacing: -.055em;
        }

        .hero-title span { color: #56d6bc; }

        .hero-copy {
            max-width: 620px;
            color: rgba(255, 255, 255, .72);
            font-size: clamp(1rem, 1.35vw, 1.12rem);
            line-height: 1.7;
        }

        .feature-chip {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            padding: .7rem .85rem;
            border-radius: 12px;
            color: rgba(255, 255, 255, .84);
            font-size: .82rem;
            font-weight: 600;
            background: rgba(255, 255, 255, .08);
        }

        .feature-chip i { color: #56d6bc; }

        .metric-card {
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 22px;
            background: rgba(255, 255, 255, .08);
            backdrop-filter: blur(12px);
        }

        .metric-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            color: #062f52;
            background: #56d6bc;
        }

        .form-panel {
            position: relative;
            min-height: 560px;
            background:
                radial-gradient(circle at 100% 0, rgba(17, 103, 177, .10), transparent 27%),
                radial-gradient(circle at 0 100%, rgba(38, 185, 154, .08), transparent 28%),
                var(--panel);
        }

        .login-form-wrap { width: min(100%, 470px); }

        .form-label {
            color: #26354f;
            font-size: .86rem;
            font-weight: 700;
        }

        .input-shell {
            display: flex;
            align-items: center;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: rgba(255, 255, 255, .88);
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .input-shell:focus-within {
            border-color: rgba(17, 103, 177, .65);
            background: #fff;
            box-shadow: 0 0 0 .25rem rgba(17, 103, 177, .10);
        }

        .input-shell > i {
            padding-left: 1rem;
            color: #7d90aa;
            font-size: 1.05rem;
        }

        .input-shell .form-control {
            min-height: 54px;
            border: 0;
            padding-left: .85rem;
            color: var(--ink);
            background: transparent;
            box-shadow: none;
        }

        .input-shell .form-control::placeholder { color: #9ba9bc; }

        .password-toggle {
            border: 0;
            padding: .75rem 1rem;
            color: #7d90aa;
            background: transparent;
        }

        .btn-login {
            min-height: 54px;
            border: 0;
            border-radius: 14px;
            font-weight: 700;
            letter-spacing: .01em;
            background: linear-gradient(100deg, var(--brand-dark), var(--brand));
            box-shadow: 0 14px 28px rgba(17, 103, 177, .22);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .btn-login:hover,
        .btn-login:focus-visible {
            transform: translateY(-2px);
            background: linear-gradient(100deg, #073b6c, #0e5fa4);
            box-shadow: 0 18px 32px rgba(17, 103, 177, .28);
        }

        .security-note {
            color: var(--muted);
            font-size: .78rem;
        }

        .security-note i { color: var(--accent); }

        @media (max-width: 991.98px) {
            .login-shell { padding: 0; }
            .login-card { min-height: 100vh; border-radius: 0; }
            .visual-panel { min-height: 340px; }
            .metric-card { display: none; }
            .form-panel { min-height: 660px; }
        }

        @media (max-width: 575.98px) {
            .visual-panel { min-height: 280px; }
            .hero-title { font-size: 2.45rem; }
            .hero-copy { font-size: .95rem; }
            .feature-chip:nth-child(n+3) { display: none; }
        }
    </style>
</head>
<body>
    <main class="login-shell d-flex align-items-center">
        <section class="login-card container-fluid p-0" aria-labelledby="login-title">
            <div class="row g-0 h-100">
                <aside class="visual-panel col-lg-7 d-flex flex-column justify-content-between p-4 p-sm-5 p-xl-6">
                    <div class="d-flex align-items-center gap-3">
                        <span class="brand-mark" aria-hidden="true"><i class="bi bi-wallet2"></i></span>
                        <div>
                            <div class="brand-name fw-bold fs-5">LogicFin</div>
                            <div class="text-white-50 small text-uppercase" style="letter-spacing:.12em">Contas a pagar</div>
                        </div>
                    </div>

                    <div class="py-5">
                        <span class="eyebrow"><i class="bi bi-stars text-warning"></i> Gestão financeira inteligente</span>
                        <h2 class="hero-title">Suas contas em dia. <span>Seu negócio no controle.</span></h2>
                        <p class="hero-copy mb-4">Centralize vencimentos, pagamentos e fornecedores em uma experiência simples, segura e feita para decisões mais tranquilas.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="feature-chip"><i class="bi bi-calendar2-check"></i> Vencimentos</span>
                            <span class="feature-chip"><i class="bi bi-bar-chart-line"></i> Fluxo financeiro</span>
                            <span class="feature-chip"><i class="bi bi-shield-check"></i> Acesso seguro</span>
                        </div>
                    </div>

                    <div class="metric-card p-3 p-xl-4">
                        <div class="d-flex align-items-center gap-3">
                            <span class="metric-icon"><i class="bi bi-graph-up-arrow"></i></span>
                            <div>
                                <div class="fw-bold">Visão clara de cada compromisso</div>
                                <div class="small text-white-50">Organização para pagar no prazo e planejar com confiança.</div>
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="form-panel col-lg-5 d-flex align-items-center justify-content-center p-4 p-sm-5">
                    <div class="login-form-wrap">
                        <div class="d-lg-none d-flex align-items-center gap-3 mb-5">
                            <span class="brand-mark" aria-hidden="true"><i class="bi bi-wallet2"></i></span>
                            <div><div class="brand-name fw-bold fs-5">LogicFin</div><div class="text-secondary small">CONTAS A PAGAR</div></div>
                        </div>

                        <p class="text-uppercase fw-bold small mb-2" style="color:var(--brand); letter-spacing:.1em">Área segura</p>
                        <h1 id="login-title" class="display-6 fw-bold mb-2">Bem-vindo</h1>
                        <p class="mb-5" style="color:var(--muted)">Entre com suas credenciais para acessar o sistema.</p>

                        <form novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-shell">
                                    <i class="bi bi-person" aria-hidden="true"></i>
                                    <input type="text" class="form-control" id="email" name="email" placeholder="Digite seu usuário" autocomplete="username" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="senha" class="form-label">Senha</label>
                                    <a href="#" class="small text-decoration-none">Esqueceu a senha?</a>
                                </div>
                                <div class="input-shell">
                                    <i class="bi bi-key" aria-hidden="true"></i>
                                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite sua senha" autocomplete="current-password" required>
                                    <button type="button" class="password-toggle" id="togglePassword" aria-label="Mostrar senha" aria-pressed="false">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="lembrar" name="lembrar">
                                <label class="form-check-label small" for="lembrar">Manter acesso neste dispositivo</label>
                            </div>

                            <button class="btn btn-primary btn-login w-100" type="button" onclick="login()">
                                Entrar no sistema <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i>
                            </button>
                        </form>

                        <p class="security-note text-center mt-4 mb-0"><i class="bi bi-shield-check me-1"></i> Acesso protegido e restrito a usuários autorizados</p>
                        <p class="text-center text-secondary small mt-5 mb-0">© <?= date('Y') ?> LogicFin</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/4.0.0/jquery.min.js"></script>
    <script>
        const toggle = document.getElementById('togglePassword');
        const password = document.getElementById('senha');

        toggle.addEventListener('click', () => {
            const show = password.type === 'password';
            password.type = show ? 'text' : 'password';
            toggle.setAttribute('aria-label', show ? 'Ocultar senha' : 'Mostrar senha');
            toggle.setAttribute('aria-pressed', String(show));
            toggle.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>
    <script src="assets/js/login.js"></script>
</body>
</html>
