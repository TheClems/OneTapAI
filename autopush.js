const chokidar = require("chokidar");
const simpleGit = require("simple-git");
const git = simpleGit();

let isPushing = false;

chokidar.watch(".", {
  ignored: /(^|[\/\\])\../, // ignore les fichiers .git, .DS_Store, etc.
  ignoreInitial: true,
  persistent: true,
}).on("all", async (event, path) => {
  if (isPushing) return;

  isPushing = true;
  console.log(`[auto-push] Changement détecté : ${event} sur ${path}`);

  try {
    await git.add(".");
    await git.commit("📝 auto-push: modification détectée", { "--no-verify": null });
    await git.push();
    console.log(`[auto-push] Modifications poussées avec succès.`);
  } catch (err) {
    console.error("[auto-push] Erreur :", err.message || err);
  }

  isPushing = false;
});
