<?php
$roles = [
    [
        'name' => 'Écrivain',
        'category' => 'Créatif',
        'model' => 'gpt-3.5-turbo',
        'instructions' => 'Tu es un écrivain professionnel. Tu aides à rédiger des histoires, romans, articles.',
        'icon' => '✍️'
    ],
    [
        'name' => 'Développeur web',
        'category' => 'Technique',
        'model' => 'claude-3-opus',
        'instructions' => 'Tu es un développeur web expert. Tu aides à coder, déboguer, et conseiller en dev.',
        'icon' => '💻'
    ],
    [
        'name' => 'Traducteur',
        'category' => 'Linguistique',
        'model' => 'gpt-4',
        'instructions' => 'Tu es un traducteur professionnel. Tu traduis des textes avec précision.',
        'icon' => '🌐'
    ],
    // Ajoute 37 autres rôles ici avec icônes si tu veux
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Choix du rôle IA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 p-6" x-data="roleSearch()">

    <h1 class="text-3xl font-bold mb-6">Choisir un rôle pour l'IA</h1>

    <input type="text" x-model="search"
           placeholder="Rechercher un métier..."
           class="w-full mb-6 p-3 rounded border border-gray-300 shadow" />

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="role in filteredRoles" :key="role.name">
            <div @click="selectedRole = role"
                 class="bg-white p-4 rounded-lg shadow hover:bg-blue-50 cursor-pointer">
                <h2 class="text-xl font-semibold">
                    <span x-text="role.icon"></span> <span x-text="role.name"></span>
                </h2>
                <p class="text-gray-500" x-text="role.category"></p>
            </div>
        </template>
    </div>

    <!-- Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
         x-show="selectedRole"
         x-transition
         @click.self="selectedRole = null">
        <div class="bg-white w-full max-w-lg p-6 rounded-lg shadow-lg relative">
            <button @click="selectedRole = null"
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-xl">&times;</button>

            <h2 class="text-2xl font-bold mb-2">
                <span x-text="selectedRole.icon"></span> <span x-text="selectedRole.name"></span>
            </h2>
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

<script>
function roleSearch() {
    const roles = <?php echo json_encode($roles); ?>;

    const normalize = str =>
        str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();

    return {
        search: '',
        selectedRole: null,
        get filteredRoles() {
            if (!this.search) return roles;
            const query = normalize(this.search);
            return roles.filter(role =>
                normalize(role.name).includes(query)
            );
        }
    };
}
</script>

</body>
</html>
