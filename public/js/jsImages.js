
window.addEventListener("load", () => {
  let currentIndex = 0; // Index de l'image actuellement affichée
  let images = []; // Tableau pour stocker les images

  // Récupère la liste des images depuis le serveur
  fetch("/images/list")
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.json();
    })
    
    .then((data) => {
      images = data.filter((image) => image !== ".DS_Store"); // Filtre .DS_Store
      images = data; // Stocke les images récupérées

      // Sélectionne l'élément ul dans le DOM
      let rootUl = document.querySelector("#singleGallery ul");
      // Sélectionne l'élément h1 dans le DOM
      let galTitle = document.querySelector("#singleGallery h2");
      // Définit un titre pour la galerie
      galTitle.textContent = "Nos plus beaux événements!";
      // Ajoute une classe pour enlever les puces
      rootUl.classList.add("list-unstyled", "row", "justify-content-center");

      // Boucle sur les images récupérées
      images.forEach((image, index) => {
        // Crée un nouvel élément li
        let theLi = document.createElement("li");
        // Ajoute les classes Bootstrap aux éléments <li>
        theLi.classList.add(
          "col-sm-12",
          "col-lg-6",
          "col-xl-4",
          "mb-4",
          "d-flex",
          "justify-content-center",
          "g-4"
        );
        // Crée un nouvel élément img
        let theImg = document.createElement("img");
        // Définit la source de l'image
        theImg.src = `images/Originales/${image}`;
        // Ajoute un écouteur d'événement pour le clic sur l'image
        theImg.addEventListener("click", () => showSinglePict(index));
        // Change le curseur en pointeur au survol de l'image
        theImg.style.cursor = "pointer";
        // Limite la hauteur de l'image à 550px
        theImg.style.height = "550px";
        // Ajuste la largeur automatiquement pour maintenir le ratio
        // theImg.style.minHeight = "550px";
        theImg.style.objectFit = "cover";
        theImg.style.width = "auto";
        // Ajoutez une ombre
        theImg.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.1)";
        // Ajoutez rounded pour des coins arrondis
        theImg.classList.add("img-fluid", "rounded", "img-thumbnail");
        // Ajoute l'image au li
        theLi.appendChild(theImg);
        // Ajoute le li à l'ul
        rootUl.appendChild(theLi);
      });
    })
    .catch((error) => console.error("Error fetching images:", error));

  // Fonction pour afficher une image en grand
  function showSinglePict(index) {
    currentIndex = index; // Met à jour l'index de l'image actuelle
    let image = images[index]; // Récupère l'image à afficher

    // Sélectionner l'élément modal dans le DOM
    let modal = document.getElementById("imageModal");

    // Sélectionner l'élément img à l'intérieur de la modal
    let modalImg = document.getElementById("modalImage");

    let watermark = document.getElementById("watermark");

    // Afficher la modal
    modal.style.display = "block";

    // Définir la source de l'image dans la modal comme celle de l'image cliquée
    modalImg.src = `images/Originales/${image}`;

    // Afficher le filigrane
    watermark.style.display = "block";

    // Sélectionner le bouton de fermeture (X) dans la modal
    let span = document.getElementsByClassName("close")[0];

    // Ajouter un écouteur d'événement pour fermer la modal quand on clique sur le X
    span.onclick = function () {
      modal.style.display = "none";
      // Cacher le filigrane quand on ferme la modal
      watermark.style.display = "none";
    };

    // Ajouter un écouteur d'événement sur la fenêtre entière
    window.onclick = function (event) {
      // Si on clique en dehors de l'image (sur la modal elle-même)
      if (event.target == modal) {
        // Fermer la modal
        modal.style.display = "none";
        // Cacher le filigrane quand on ferme la modal
        watermark.style.display = "none";
      }
    };
  }

  // Fonction pour afficher l'image précédente
  function showPrevImage() {
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    showSinglePict(currentIndex);
  }

  // Fonction pour afficher l'image suivante
  function showNextImage() {
    currentIndex = (currentIndex + 1) % images.length;
    showSinglePict(currentIndex);
  }

  // Ajouter des écouteurs d'événements pour les boutons de navigation
  document.getElementById("prevImage").onclick = showPrevImage;
  document.getElementById("nextImage").onclick = showNextImage;

  // Désactiver le clic droit sur les images
  document.addEventListener(
    "contextmenu",
    function (e) {
      if (e.target.tagName === "IMG") {
        e.preventDefault();
      }
    },
    false
  );

  // Désactiver le glisser-déposer des images
  document.addEventListener(
    "dragstart",
    function (e) {
      if (e.target.tagName === "IMG") {
        e.preventDefault();
      }
    },
    false
  );
  // contextmenu : clic de droite de la souris

$( '#hover'). contextmenu (function(event) {
  // empecher l'apparition du menu
  event.preventDefault();
  console.log( 'clic de droite');
  alert('Clic de droite annulé');
  });
});
