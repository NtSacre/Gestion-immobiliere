<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ImmoApp</title>
     <link rel="icon" href="/assets/images/favicon.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'construction-yellow': '#FFD700',
                        'construction-dark-yellow': '#FFC107',
                        'construction-black': '#1A1A1A',
                        'construction-gray': '#2D2D2D'
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-yellow { background: linear-gradient(135deg, #FFD700 0%, #FFC107 100%); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); }
        .modal { backdrop-filter: blur(8px); background: rgba(0, 0, 0, 0.6); }
        .input-focus:focus { border-color: #FFD700; box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1); }
        .btn-primary { background: linear-gradient(135deg, #FFD700 0%, #FFC107 100%); color: #1A1A1A; font-weight: 600; transition: all 0.3s ease; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3); }
        .btn-secondary { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; font-weight: 500; transition: all 0.3s ease; }
        .btn-secondary:hover { background: #e5e7eb; border-color: #9ca3af; }
        .btn-danger { background: #ef4444; color: white; font-weight: 500; transition: all 0.3s ease; }
        .btn-danger:hover { background: #dc2626; transform: translateY(-1px); }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4">
        <?php include __DIR__ . '/../' . $content_view; ?>
    </div>
</body>
</html>