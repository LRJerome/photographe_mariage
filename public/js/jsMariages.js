// Attendre que la page soit entièrement chargée
window.addEventListener("load", () => {
  // Désactiver le clic droit sur toute la page
  document.addEventListener('contextmenu', disableRightClick, false);
  
  // Initialiser les variables pour l'index actuel et l'ID de la catégorie
  let currentIndex = 0;
  let currentCategoryId = categoryId; 
  let flatImages = [];

  // Fonction pour désactiver le clic droit
  function disableRightClick(event) {
    event.preventDefault();
    console.log('Clic droit désactivé');
    // Vous pouvez également afficher une alerte si vous le souhaitez
    // alert('Clic droit désactivé');
  }

  // Récupérer les images de la catégorie depuis le serveur
  fetch(`/Images/Mariages/${currentCategoryId}`)
    .then((response) => {
      // Vérifier si la réponse est valide
      if (!response.ok) {
        throw new Error("La réponse du réseau n'était pas correcte");
      }
      return response.json();
    })
    .then((data) => {
      console.log("Données reçues:", data);

      // Traiter les données reçues
      if (typeof data === 'object' && data[currentCategoryId]) {
        images = data;
        flatImages = data[currentCategoryId];
      } else {
        images = { [currentCategoryId]: data };
        flatImages = data;
      }
      console.log("Images dans la catégorie:", flatImages);

      // Sélectionner les éléments du DOM
      let rootUl = document.querySelector("#singleGallery ul");
      let galTitle = document.querySelector("#singleGallery h2");
      
      // Récupérer le nom de la catégorie
      fetch(`/api/category/${currentCategoryId}`)
        .then(response => {
          if (!response.ok) {
            throw new Error(`Erreur HTTP! statut: ${response.status}`);
          }
          return response.json();
        })
        .then(categoryInfo => {
          console.log("Informations de catégorie reçues:", categoryInfo);
          galTitle.textContent = `Photos du mariage de : "${categoryInfo.name}"`;
        })
        .catch(error => {
          console.error('Erreur lors de la récupération des informations de la catégorie:', error);
          galTitle.textContent = `Photos du mariage - Catégorie ${currentCategoryId}`;
        });
    
      // Ajouter des classes à la liste racine
      rootUl.classList.add("list-unstyled", "row", "justify-content-center");
    
      // Créer et ajouter chaque image à la galerie
      flatImages.forEach((image, index) => {
        let theLi = document.createElement("li");
        theLi.classList.add(
          "col-sm-12", "col-lg-6", "col-xl-4", "mb-4",
          "d-flex", "justify-content-center", "g-4"
        );
    
        let theImg = document.createElement("img");
        theImg.src = '/' + image.path;
        console.log("Source de l'image:", theImg.src);
        theImg.addEventListener("click", () => showSinglePict(currentCategoryId, index));
        theImg.style.cursor = "pointer";
        theImg.style.height = "550px";
        theImg.style.objectFit = "cover";
        theImg.style.width = "auto";
        theImg.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.1)";
        theImg.classList.add("img-fluid", "rounded", "img-thumbnail");
    
        theLi.appendChild(theImg);
        rootUl.appendChild(theLi);
      });
    })
    .catch((error) => console.error("Erreur lors de la récupération des images:", error));

  // Fonction pour afficher une image individuelle
  function showSinglePict(categoryId, index) {
    currentCategoryId = categoryId;
    currentIndex = index;
    let image = flatImages[index];

    let modal = document.getElementById("imageModal");
    let modalImg = document.getElementById("modalImage");
    let watermark = document.getElementById("watermark");
    let galTitle = document.querySelector("#singleGallery h2");

    modal.style.display = "block";
    modalImg.src = '/' + image.path;
    watermark.style.display = "block";

    // Récupérer les informations de la catégorie
    fetch(`/api/category/${categoryId}`)
      .then(response => {
        if (!response.ok) {
          throw new Error(`Erreur HTTP! statut: ${response.status}`);
        }
        return response.json();
      })
      .then(categoryInfo => {
        // Mettre à jour le titre avec le nom de la catégorie
        galTitle.textContent = `Photos du mariage de - ${categoryInfo.name}`;
      })
      .catch(error => {
        console.error('Erreur lors de la récupération des informations de la catégorie:', error);
        galTitle.textContent = `Photos du mariage - Catégorie ${categoryId}`;
      });

    // Gestion de la fermeture de la modal
    let span = document.getElementsByClassName("close")[0];
    span.onclick = function() {
      modal.style.display = "none";
      watermark.style.display = "none";
    };

    // Fermeture de la modal en cliquant en dehors de l'image
    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = "none";
        watermark.style.display = "none";
      }
    };
  }

  // Fonction pour afficher l'image précédente
  function showPrevImage() {
    currentIndex = (currentIndex - 1 + flatImages.length) % flatImages.length;
    showSinglePict(currentCategoryId, currentIndex);
  }
  
  // Fonction pour afficher l'image suivante
  function showNextImage() {
    currentIndex = (currentIndex + 1) % flatImages.length;
    showSinglePict(currentCategoryId, currentIndex);
  }

  // Ajouter des écouteurs d'événements pour les boutons précédent et suivant
  document.getElementById("prevImage").onclick = showPrevImage;
  document.getElementById("nextImage").onclick = showNextImage;

  // Désactiver le clic droit sur les images de la galerie
  document.querySelector("#singleGallery").addEventListener('contextmenu', function(event) {
    if (event.target.tagName === 'IMG') {
      disableRightClick(event);
    }
  }, false);

  // Désactiver le clic droit sur l'ensemble de la modal
  document.getElementById("imageModal").addEventListener('contextmenu', disableRightClick, false);

  // Désactiver le glisser-déposer des images
  document.addEventListener('dragstart', function(event) {
    if (event.target.tagName === 'IMG') {
      event.preventDefault();
    }
  }, false);
});