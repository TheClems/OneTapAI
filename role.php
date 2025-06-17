<?php
// Exemple de structure des rôles (en temps réel, ça peut venir d'une base de données)
$roles = [
    [
        'name' => 'Écrivain',
        'category' => 'Créatif',
        'model' => 'gpt-3.5-turbo',
        'instructions' => 'Tu es un écrivain professionnel. Tu aides à rédiger des histoires, romans, articles.'
    ],
    [
        'name' => 'Développeur web',
        'category' => 'Technique',
        'model' => 'claude-3-opus',
        'instructions' => 'Tu es un développeur web expert. Tu aides à coder, déboguer, et conseiller en dev.'
    ],
    [
        'name' => 'Traducteur',
        'category' => 'Linguistique',
        'model' => 'gpt-4',
        'instructions' => 'Tu es un traducteur professionnel. Tu traduis des textes avec précision.'
    ],
    // ... ajoute ici jusqu'à 40 rôles avec leurs infos ...
];

// On peut grouper les rôles par catégorie si tu veux une UI plus claire
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Choix du rôle IA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 p-6" x-data="{ search: '', selectedRole: null }">

    <h1 class="text-3xl font-bold mb-6">Choisir un rôle pour l'IA</h1>

    <!-- Barre de recherche -->
    <input type="text" x-model="search" placeholder="Rechercher un métier..."
        class="w-full mb-6 p-3 rounded border border-gray-300 shadow" />

    <!-- Liste des rôles -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($roles as $role): ?>
            <div
                class="bg-white p-4 rounded-lg shadow hover:bg-blue-50 cursor-pointer"
                x-show="search === '' || '<?= strtolower($role['name']) ?>'.includes(search.toLowerCase())"
                @click="selectedRole = <?= htmlspecialchars(json_encode($role)) ?>">

                <h2 class="text-xl font-semibold"><?= htmlspecialchars($role['name']) ?></h2>
                <p class="text-gray-500"><?= htmlspecialchars($role['category']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
         x-show="selectedRole"
         x-transition
         @click.self="selectedRole = null">
        <div class="bg-white w-full max-w-lg p-6 rounded-lg shadow-lg relative">
            <button @click="selectedRole = null"
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-xl">&times;</button>

            <h2 class="text-2xl font-bold mb-2" x-text="selectedRole.name"></h2>
            <p class="text-sm text-gray-400 mb-4" x-text="'Catégorie : ' + selectedRole.category"></p>

            <div class="mb-4">
                <h3 class="font-semibold">Modèle utilisé :</h3>
                <p x-text="selectedRole.model" class="text-blue-700"></p>
            </div>

            <div class="mb-4">
                <h3 class="font-semibold">Instructions :</h3>
                <p x-text="selectedRole.instructions" class="text-gray-700 whitespace-pre-line"></p>
            </div>

            <a :href="'chat.php?role=' + encodeURIComponent(selectedRole.name)"
               class="block text-center mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Lancer la conversation
            </a>
        </div>
    </div>

</body>
</html>
