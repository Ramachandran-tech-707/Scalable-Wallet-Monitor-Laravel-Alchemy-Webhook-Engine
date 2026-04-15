<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Alchemy Webhooks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Bootstrap JS (with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</head>

<style>
    body {
        background: linear-gradient(to right, #f0f4ff, #ffffff);
        min-height: 100vh;
    }

    .incrase-width {
        max-width: 1800px !important;
    }
</style>

<body>

    <!-- Navbar Section -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <!-- Brand -->
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="bi bi-lightning-charge-fill"></i> Alchemy Panel
            </a>

            <!-- Toggler -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menu Items -->
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>

                    <!-- Webhook History -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('webhooks.history') }}">
                            <i class="bi bi-clock-history"></i> Webhook History
                        </a>
                    </li>

                    <!-- Webhooks Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="webhooksDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-diagram-3"></i> Webhooks
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="webhooksDropdown">
                            <!-- Wallet History -->
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.webhook.manage') }}">
                                    <i class="bi bi-sliders"></i> Manage WebHooks
                                </a>
                            </li>

                            <!-- Wallet History -->
                            <li>
                                <a class="dropdown-item" href="{{ route('wallets.index') }}">
                                    <i class="bi bi-wallet2"></i> Wallet Activity
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Custom Variables Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="variablesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-database-fill"></i> Custom Variables
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="variablesDropdown">

                            <li>
                                <a class="dropdown-item" href="{{ route('variables.index') }}">
                                    <i class="bi bi-list-check"></i> Custom Variables Lists
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="{{ route('variables.manage') }}">
                                    <i class="bi bi-gear-fill"></i> Manage Custom Variables
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('variables.webhook.create') }}">
                                    <i class="bi bi-broadcast-pin"></i> Custom Variable Webhooks
                                </a>
                            </li>
                        </ul>
                    </li>
                    

                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    @yield('content')

    @stack('scripts')
</body>

</html>