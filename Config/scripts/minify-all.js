const fs = require('fs');
const path = require('path');
const { minify } = require('terser');

const rootDir = path.resolve(__dirname);

// Nombres de carpetas pro
const devDirName = 'dev-js';
const prodDirName = ''; // No necesitamos una carpeta especial para los archivos minificados, se guardan directamente en el directorio original

// Ignorar ciertas carpetas
const ignoredDirs = ['node_modules', '.git', devDirName, 'Vendor', 'Test', 'Config', 'Models',];

// Función para minificar el contenido de un archivo
async function minifyJS(filePath) {
    const code = fs.readFileSync(filePath, 'utf8');
    try {
        const result = await minify(code);
        return result.code;
    } catch (err) {
        console.error(`❌ Error al minificar ${filePath}: ${err.message}`);
        return null;
    }
}

// Función para asegurar que la carpeta exista
function ensureDirExists(dirPath) {
    if (!fs.existsSync(dirPath)) {
        fs.mkdirSync(dirPath, { recursive: true });
    }
}

// Función para procesar un directorio
async function processDirectory(dir) {
    const entries = fs.readdirSync(dir, { withFileTypes: true });

    for (const entry of entries) {
        const fullPath = path.join(dir, entry.name);

        if (entry.isDirectory()) {
            if (!ignoredDirs.includes(entry.name)) {
                await processDirectory(fullPath);
            }
        } else if (entry.isFile() && entry.name.endsWith('.js')) {
            const relativeDir = path.relative(rootDir, path.dirname(fullPath));
            const devDir = path.join(rootDir, relativeDir, devDirName); // Carpeta dev-js
            ensureDirExists(devDir);

            const devTarget = path.join(devDir, entry.name); // El archivo movido a dev-js
            const prodTarget = path.join(dir, entry.name.replace(/\.js$/, '-min.js')); // Archivo minificado en el mismo directorio de `Controllers/`

            // Mover el archivo original a dev-js
            if (!fs.existsSync(devTarget)) {
                fs.renameSync(fullPath, devTarget);
                console.log(`✅ Movido a dev-js: ${path.relative(rootDir, devTarget)}`);
            }

            // Minificar y guardar el archivo en el directorio original
            const minifiedCode = await minifyJS(devTarget);
            if (minifiedCode) {
                fs.writeFileSync(prodTarget, minifiedCode, 'utf8');
                console.log(`✅ Minificado: ${path.relative(rootDir, prodTarget)}`);
            }
        }
    }
}

// Ejecutar el script
(async () => {
    console.log('🚀 Procesando archivos JS...');
    await processDirectory(rootDir);
    console.log('🎉 ¡Proceso completado!');
})();
