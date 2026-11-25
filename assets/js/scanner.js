// Scanner IA : reconnaît l'objet sur la photo avec MobileNet (TensorFlow.js), directement dans le navigateur

const fileInput = document.getElementById('file');
const photo = document.getElementById('photo');
const placeholder = document.getElementById('placeholder');
const statusText = document.getElementById('status');
const guesses = document.getElementById('guesses');
const hiddenFields = document.getElementById('hidden-fields');
const sendButton = document.getElementById('send');
let model = null;

// Ajoute un champ caché au formulaire (ce que l'IA a vu, envoyé au serveur PHP)
function addHidden(name, value) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    hiddenFields.appendChild(input);
}

// 1. On charge le modèle une seule fois, au chargement de la page
//    (calcul sur le processeur "cpu" : fonctionne sur tous les PC, une photo suffit)
tf.setBackend('cpu')
    .then(() => mobilenet.load({ version: 2, alpha: 1.0 }))
    .then((loaded) => {
        model = loaded;
        statusText.textContent = "L'IA est prête. Prends une photo de ton déchet.";
    })
    .catch(() => {
        statusText.textContent = "Impossible de charger l'IA : vérifie ta connexion Internet.";
    });

// 2. Quand l'utilisateur choisit une photo, on l'affiche
fileInput.addEventListener('change', () => {
    const file = fileInput.files[0];
    if (!file) return;
    photo.src = URL.createObjectURL(file);
    placeholder.hidden = true;
    sendButton.disabled = true;
    guesses.innerHTML = '';
    statusText.textContent = 'Analyse en cours…';
});

// 3. Quand la photo est affichée, l'IA la classe et donne ses 3 meilleures réponses
photo.addEventListener('load', async () => {
    if (!model) {
        statusText.textContent = "L'IA charge encore, réessaie dans un instant.";
        return;
    }
    const predictions = await model.classify(photo, 3);
    hiddenFields.innerHTML = '';
    predictions.forEach((p) => {
        const percent = Math.round(p.probability * 100);
        const li = document.createElement('li');
        li.innerHTML = '<span></span><span class="bar"><i></i></span><b></b>';
        li.children[0].textContent = p.className;
        li.querySelector('i').style.width = percent + '%';
        li.children[2].textContent = percent + ' %';
        guesses.appendChild(li);
        addHidden('labels[]', p.className);
        addHidden('probas[]', p.probability);
    });
    statusText.textContent = "Voici ce que l'IA voit :";
    sendButton.disabled = false;
});
