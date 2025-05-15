const fs = require('fs');
const path = require('path');
const { minify } = require('terser');

const rootDir = path.resolve(__dirname);
const devDirName = 'dev-js';

// Función para minificar el contenido JS
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

// Procesa todos los dev-js y reemplaza el .min.js en la carpeta padre
async function processDirectory(dir) {
    const entries = fs.readdirSync(dir, { withFileTypes: true });

    for (const entry of entries) {
        const fullPath = path.join(dir, entry.name);

        if (entry.isDirectory()) {
            if (entry.name === devDirName) {
                const devFiles = fs.readdirSync(fullPath);
                for (const file of devFiles) {
                    const devFilePath = path.join(fullPath, file);

                    // Saltar archivos ya minificados
                    if (!file.endsWith('.js') || file.endsWith('.min.js')) continue;

                    const baseFileName = path.basename(file, '.js');
                    const minFileName = `${baseFileName}-min.js`;
                    const parentDir = path.dirname(fullPath);
                    const minFilePath = path.join(parentDir, minFileName);

                    const minifiedCode = await minifyJS(devFilePath);
                    if (minifiedCode) {
                        fs.writeFileSync(minFilePath, minifiedCode, 'utf8');
                        console.log(`✅ Minificado: ${path.relative(rootDir, minFilePath)}`);
                    }
                }
            } else {
                await processDirectory(fullPath);
            }
        }
    }
}

// Ejecutar
(async () => {
    console.log('🔁 Re-minificando archivos de dev-js...');
    await processDirectory(rootDir);
    console.log('🎉 Listo: Todos los .min.js fueron actualizados sin duplicados.');
})();
