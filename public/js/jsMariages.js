// Attendre que la page soit entièrement chargée
window.addEventListener("load", () => {
  // Désactiver le clic droit sur toute la page
  document.addEventListener('contextmenu', disableRightClick, false);
  
  // Variables globales
  let currentIndex = 0;
  let currentCategoryId = categoryId; 
  let flatImages = [];
  let isAutoPlaying = false;
  let autoPlayInterval;
  const weddingDate = new Date(categoryWeddingDateAt * 1000);

  // Fonction pour désactiver le clic droit
  function disableRightClick(event) {
    event.preventDefault();
    alert('Clic droit désactivé');
  }

  // Récupérer les images de la catégorie depuis le serveur
  fetch(`/Images/Mariages/${currentCategoryId}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error("La réponse du réseau n'était pas correcte");
      }
      return response.json();
    })
    .then((data) => {
      if (typeof data === 'object' && data[currentCategoryId]) {
        images = data;
        flatImages = data[currentCategoryId];
      } else {
        images = { [currentCategoryId]: data };
        flatImages = data;
      }

      let rootUl = document.querySelector("#singleGallery ul");
      let galTitle = document.querySelector("#singleGallery h2");

      fetch(`/api/category/${currentCategoryId}`)
        .then(response => {
          if (!response.ok) {
            throw new Error(`Erreur HTTP! statut: ${response.status}`);
          }
          return response.json();
        })
        .then(categoryInfo => {
          console.log("Informations de catégorie reçues:", categoryInfo);
          galTitle.textContent = `Photos du mariage de ${categoryInfo.name} qui a eu lieu le : ${weddingDate.toLocaleDateString('fr-FR')}`;
        })
        .catch(error => {
          console.error('Erreur lors de la récupération des informations de la catégorie:', error);
          galTitle.textContent = `Photos du mariage - Catégorie ${currentCategoryId}`;
        });

      rootUl.classList.add("list-unstyled", "row", "justify-content-center");

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
    updateModalImage();

    let modal = document.getElementById("imageModal");
    let watermark = document.getElementById("watermark");
    let galTitle = document.querySelector("#singleGallery h2");

    modal.style.display = "block";
    watermark.style.display = "block";

    fetch(`/api/category/${categoryId}`)
      .then(response => {
        if (!response.ok) {
          throw new Error(`Erreur HTTP! statut: ${response.status}`);
        }
        return response.json();
      })
      .then(categoryInfo => {
        galTitle.textContent = `Photos du mariage de - ${categoryInfo.name} qui a eu lieu le : ${weddingDate.toLocaleDateString('fr-FR')}`;
      })
      .catch(error => {
        console.error('Erreur lors de la récupération des informations de la catégorie:', error);
        galTitle.textContent = `Photos du mariage - Catégorie ${categoryId}`;
      });

    let span = document.getElementsByClassName("close")[0];
    span.onclick = function() {
      modal.style.display = "none";
      watermark.style.display = "none";
      stopAutoPlay();
    };

    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = "none";
        watermark.style.display = "none";
        stopAutoPlay();
      }
    };
  }

  // Fonction pour mettre à jour l'image dans la modale
  function updateModalImage() {
    let modalImg = document.getElementById("modalImage");
    modalImg.src = '/' + flatImages[currentIndex].path;
  }

  // Fonction pour afficher l'image précédente
  function showPrevImage() {
    currentIndex = (currentIndex - 1 + flatImages.length) % flatImages.length;
    updateModalImage();
  }
  
  // Fonction pour afficher l'image suivante
  function showNextImage() {
    currentIndex = (currentIndex + 1) % flatImages.length;
    updateModalImage();
  }

  // Fonction pour démarrer la lecture automatique
  function startAutoPlay() {
    if (!isAutoPlaying) {
      isAutoPlaying = true;
      autoPlayInterval = setInterval(showNextImage, 2500); // Change d'image toutes les 2.5 secondes
      document.getElementById("startSlideshow").textContent = "Arrêter le diaporama";
    } else {
      stopAutoPlay();
    }
  }

  // Fonction pour arrêter la lecture automatique
  function stopAutoPlay() {
    isAutoPlaying = false;
    clearInterval(autoPlayInterval);
    document.getElementById("startSlideshow").textContent = "Démarrer le diaporama";
  }

  // Ajouter des écouteurs d'événements pour les boutons précédent et suivant
  document.getElementById("prevImage").addEventListener('click', showPrevImage);
  document.getElementById("nextImage").addEventListener('click', showNextImage);
  document.getElementById("startSlideshow").addEventListener('click', startAutoPlay);

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

  // Arrêter le diaporama lorsque l'onglet n'est plus actif
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      stopAutoPlay();
    }
  });
});