window.addEventListener("load", () => {
  let currentIndex = 0;
  let currentCategoryId = categoryId; // Utilisez l'ID de catégorie passé depuis Twig
  // let images = {};
  let flatImages = [];

  fetch(`/Images/Mariages/${currentCategoryId}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.json();
    })
    .then((data) => {
      console.log("Données reçues:", data);
      
      // Vérifiez si data est un objet et contient une propriété avec le categoryId
      if (typeof data === 'object' && data[currentCategoryId]) {
        images = data;
        flatImages = data[currentCategoryId];
      } else {
        // Si data est déjà un tableau d'images pour la catégorie
        images = { [currentCategoryId]: data };
        flatImages = data;
      }
    
      let rootUl = document.querySelector("#singleGallery ul");
      let galTitle = document.querySelector("#singleGallery h2");
      galTitle.textContent = `Photos du mariage de - Catégorie ${currentCategoryId}`;
    
      rootUl.classList.add("list-unstyled", "row", "justify-content-center");
    
      flatImages.forEach((image, index) => {
        let theLi = document.createElement("li");
        theLi.classList.add(
          "col-sm-12",
          "col-lg-6",
          "col-xl-4",
          "mb-4",
          "d-flex",
          "justify-content-center",
          "g-4"
        );
    
        let theImg = document.createElement("img");
        theImg.src = '/' + image.path;
        console.log("Image source:", theImg.src);
        theImg.addEventListener("click", () =>
          showSinglePict(currentCategoryId, index)
        );
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
    .catch((error) => console.error("Error fetching images:", error));

    function showSinglePict(categoryId, index) {
      currentCategoryId = categoryId;
      currentIndex = index;
      let image = flatImages[index];
    
      let modal = document.getElementById("imageModal");
      let modalImg = document.getElementById("modalImage");
      let watermark = document.getElementById("watermark");
    
      modal.style.display = "block";
      modalImg.src = '/' + image.path;
      watermark.style.display = "block";

    let span = document.getElementsByClassName("close")[0];
    span.onclick = function () {
      modal.style.display = "none";
      watermark.style.display = "none";
    };

    window.onclick = function (event) {
      if (event.target == modal) {
        modal.style.display = "none";
        watermark.style.display = "none";
      }
    };
  }

  function showPrevImage() {
    currentIndex = (currentIndex - 1 + flatImages.length) % flatImages.length;
    showSinglePict(currentCategoryId, currentIndex);
  }
  
  function showNextImage() {
    currentIndex = (currentIndex + 1) % flatImages.length;
    showSinglePict(currentCategoryId, currentIndex);
  }

  document.getElementById("prevImage").onclick = showPrevImage;
  document.getElementById("nextImage").onclick = showNextImage;

  document.addEventListener(
    "contextmenu",
    function (e) {
      if (e.target.tagName === "IMG") {
        e.preventDefault();
      }
    },
    false
  );

  document.addEventListener(
    "dragstart",
    function (e) {
      if (e.target.tagName === "IMG") {
        e.preventDefault();
      }
    },
    false
  );
});
