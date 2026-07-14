<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aguardando Aprovação - Gestor de Freelas</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/freela/freela-03.png') }}">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            550: '#16a34a',
                            600: '#15803d',
                        }
                    },
                    fontFamily: {
                        sans: ['Geist', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full text-center space-y-6 bg-white border border-slate-200 shadow-xl rounded-[5px] p-8">
        
        <!-- Logo -->
        <div class="flex justify-center">
            <span class="text-xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-green-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Gestor<span class="text-green-600">Freelas</span>
            </span>
        </div>

        <div class="space-y-3">
            <h2 class="text-xl font-extrabold text-slate-900">Aguardando Aprovação</h2>
            <p class="text-xs text-slate-550 font-bold uppercase tracking-wider">Seu cadastro foi recebido com sucesso!</p>
            <p class="text-xs text-slate-400 leading-relaxed">
                Por motivos de segurança contra acessos não autorizados e bots, novos cadastros precisam ser liberados por um administrador Master. 
                Assim que sua conta for aprovada, você terá acesso imediato aos módulos.
            </p>
        </div>

        <div class="border-t border-slate-100 pt-4">
            <p class="text-[10px] text-slate-400">Entre em contato com o administrador do sistema para acelerar sua liberação.</p>
        </div>

        <!-- Sair / Logout para retornar -->
        <div class="pt-2">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-[5px] transition-colors shadow-sm">
                    Voltar para o Login
                </button>
            </form>
        </div>
    </div>

</body>
</html>
