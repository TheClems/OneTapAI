const chokidar = require('chokidar');
const simpleGit = require('simple-git');
const git = simpleGit();

console.log("[auto-push] Script lancé...");

chokidar.watch('.', {
  ignored: /(^|[\/\\])\../, // ignore les fichiers cachés
  persistent: true
}).on('change', async (path) => {
  console.log(`[auto-push] Changement détecté : change sur ${path}`);
  try {
    await git.add('.');
    await git.commit(`Auto commit: modification sur ${path}`);
    await git.pull('origin', 'main'); // <-- le pull AVANT le push
    await git.push('origin', 'main');
    console.log('[auto-push] Push réussi !');
  } catch (err) {
    console.error('[auto-push] Erreur :', err.message || err);
  }
});
