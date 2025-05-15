const ncp = require('ncp').ncp;
const path = require('path');
const fs = require('fs');
const fse = require('fs-extra');

// Define las librerías y sus carpetas de destino
const libraries = [
  { 
    src: '../../node_modules/bootstrap/dist', 
    dest: '../../Vendor/Frontend/Bootstrap' 
  },
  { 
    src: '../../node_modules/isotope-layout/dist', 
    dest: '../../Vendor/Frontend/Isotope' 
  },
  { 
    src: '../../node_modules/owl.carousel/dist', 
    dest: '../../Vendor/Frontend/OwlCarousel' 
  },
  { 
    src: '../../node_modules/jquery/dist', 
    dest: '../../Vendor/Frontend/Jquery' 
  },
  { 
    src: '../../node_modules/animate.css', 
    dest: '../../Vendor/Frontend/Animate' 
  },
  { 
    src: '../../node_modules/@fortawesome/fontawesome-free', 
    dest: '../../Vendor/Frontend/FontAwesome' 
  },
  { 
    src: '../../node_modules/swiper', 
    dest: '../../Vendor/Frontend/Swiper' 
  },
  { 
    src: '../../node_modules/jquery-ui-dist', 
    dest: '../../Vendor/Frontend/JqueryUI' 
  },
  { 
    src: '../../node_modules/jquery-validation/dist', 
    dest: '../../Vendor/Frontend/jQueryValidation' 
  },
  { 
    src: '../../node_modules/@iconify/iconify/dist', 
    dest: '../../Vendor/Frontend/Iconify' 
  },
  { 
    src: '../../node_modules/select2/dist', 
    dest: '../../Vendor/Frontend/Select2' 
  },
  { 
    src: '../../node_modules/datatables.net-bs5/css', 
    dest: '../../Vendor/Frontend/DataTables/css' 
  },
  { 
    src: '../../node_modules/datatables.net/js', 
    dest: '../../Vendor/Frontend/DataTables/js' 
  },
  { 
    src: '../../node_modules/datatables.net-bs5/js', 
    dest: '../../Vendor/Frontend/DataTables/js-bs5' 
  },
  { 
    src: '../../node_modules/sweetalert2/dist', 
    dest: '../../Vendor/Frontend/SweetAlert' 
  },
  {
    src: '../../node_modules/fullcalendar/', 
    dest: '../../Vendor/Frontend/FullCalendar'
  },
  {
    src: '../../node_modules/@fullcalendar/core', 
    dest: '../../Vendor/Frontend/FullCalendar/core'
  },
    {
    src: '../tailwindCode/', 
    dest: '../../Vendor/Frontend/'
  }
];

// Copiar cada librería
libraries.forEach(library => {
  const source = path.join(__dirname, library.src);  // Ajusta la ruta a node_modules
  const destination = path.join(__dirname, library.dest);  // Ajusta la ruta a Vendor/Frontend

  // Crear el directorio de destino si no existe
  const fs = require('fs');
  if (!fs.existsSync(destination)) {
    fs.mkdirSync(destination, { recursive: true });
  }

  // Copiar los archivos
  ncp(source, destination, function (err) {
    if (err) {
      return console.error(`❌ Error copiando ${library.src}:`, err);
    }
    console.log(`✅ Copiado: ${library.src} -> ${destination}`);
  });
});




// ➕ MOVER vendor/ (minúscula) a Vendor/Backend/ (mayúscula)
const moveVendorBackend = async () => {
  const vendorSrc = path.join(__dirname, '../../vendor');
  const vendorDest = path.join(__dirname, '../../Vendor/Backend');

  if (fs.existsSync(vendorSrc)) {
    try {
      await fse.ensureDir(vendorDest); // Asegura que Vendor/Backend existe
      await fse.copy(vendorSrc, vendorDest, { overwrite: true });
      console.log('✅ Copiado: vendor/ -> Vendor/Backend/');

      // ❗ Opcional: Eliminar vendor/ original para evitar duplicados
      await fse.remove(vendorSrc);
      console.log('🗑️ Carpeta vendor/ original eliminada');
    } catch (err) {
      console.error('❌ Error moviendo vendor a Vendor/Backend:', err);
    }
  } else {
    console.log('ℹ️ No existe carpeta vendor/ para mover.');
  }
};

moveVendorBackend();