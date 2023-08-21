const zipdir = require('zip-dir')

async function zip() {
    await zipdir('./plugins', {
        saveTo: './plugins/backlink-cafe/backlink-cafe.zip',
        filter: (path, stat) => {
            if (path.match(/plugins\/index.php/) || path.match(/plugins\/backlink-cafe/)) {
                if (path.match(/plugins\/backlink-cafe$/)) return true
                if (path.match(/\.php$/)) return true
                if (path.includes('LICENSE.txt')) return true
                if (path.includes('README.txt')) return true
                if (path.includes('backlink-cafe.php')) return true
                if (path.includes('uninstall.php')) return true
                if (path.includes('admin')) return true
                if (path.includes('includes')) return true
                if (path.includes('languages')) return true
                if (path.includes('public')) return true
                if (path.includes('admin')) return true
                return false
            }
            return false
        }
    })
}

zip()