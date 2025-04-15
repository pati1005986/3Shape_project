class QRCode {
    constructor(element, options = {}) {
        this.svgMode = options.renderSVG || false;
        
        if(this.svgMode) {
            this.svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            this.svg.setAttribute('viewBox', `0 0 ${options.size || 256} ${options.size || 256}`);
            this.svg.setAttribute('width', options.size || 256);
            this.svg.setAttribute('height', options.size || 256);
        } else {
            this.canvas = typeof element === 'string' ? document.getElementById(element) : element;
            this.ctx = this.canvas.getContext('2d');
            this.canvas.width = options.size || 256;
            this.canvas.height = options.size || 256;
        }
        
        this.options = {
            size: options.size || 256,
            padding: options.padding || 20,
            backgroundColor: options.backgroundColor || '#ffffff',
            foregroundColor: options.foregroundColor || '#000000',
            errorCorrection: options.errorCorrection || 'M',
            renderSVG: options.renderSVG || false
        };
        this.canvas.width = this.options.size;
        this.canvas.height = this.options.size;
    }

    generate(text) {
        // Limpiar el canvas
        this.ctx.fillStyle = this.options.backgroundColor;
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);

        // Generar matriz de datos QR básica (8x8 para demostración)
        const matrix = this._generateBasicMatrix(text);
        const moduleSize = (this.options.size - 2 * this.options.padding) / matrix.length;

        // Dibujar módulos
        const color = this.options.foregroundColor;
        
        if(this.svgMode) {
            this.svg.innerHTML = '';
            const bg = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            bg.setAttribute('width', '100%');
            bg.setAttribute('height', '100%');
            bg.setAttribute('fill', this.options.backgroundColor);
            this.svg.appendChild(bg);
        } else {
            this.ctx.fillStyle = this.options.backgroundColor;
            this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
        }

        for (let row = 0; row < matrix.length; row++) {
            for (let col = 0; col < matrix[row].length; col++) {
                if (matrix[row][col]) {
                    if(this.svgMode) {
                        const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                        rect.setAttribute('x', this.options.padding + col * moduleSize);
                        rect.setAttribute('y', this.options.padding + row * moduleSize);
                        rect.setAttribute('width', moduleSize);
                        rect.setAttribute('height', moduleSize);
                        rect.setAttribute('fill', color);
                        this.svg.appendChild(rect);
                    } else {
                        this.ctx.fillStyle = color;
                        this.ctx.fillRect(
                            this.options.padding + col * moduleSize,
                            this.options.padding + row * moduleSize,
                            moduleSize,
                            moduleSize
                        );
                    }
                }
            }
        }
    }

    _generateBasicMatrix(text) {
        // Calcular versión automáticamente
        const hasNonAlphanumeric = /[^0-9A-Z $%*+\-./:]/.test(text);
        const version = this._calculateVersion(text, hasNonAlphanumeric);
        const size = 21 + (version - 1) * 4;
        const matrix = Array(size).fill().map(() => Array(size).fill(false));
        
        // Añadir patrones de alineamiento para versiones > 1
        if (version > 1) this._addAlignmentPatterns(matrix, version);

        // Patrones de posición
        this._addPositionPatterns(matrix, 0, 0);
        this._addPositionPatterns(matrix, size - 7, 0);
        this._addPositionPatterns(matrix, 0, size - 7);

        // Patrones de sincronización
        for (let i = 8; i < size - 8; i++) {
            matrix[i][6] = (i % 2) === 0;
            matrix[6][i] = (i % 2) === 0;
        }

        // Zona de formato y versión
        this._addFormatInfo(matrix, this.options.errorCorrection);

        // Codificación de datos
        const encodedData = this._encodeText(text);
        this._placeDataBits(matrix, encodedData);

        // Máscara óptima
        const maskPattern = this._selectBestMask(matrix);
        this._applyMask(matrix, maskPattern);

        return matrix;
    }

    _addPositionPatterns(matrix, x, y) {
        for (let i = 0; i < 7; i++) {
            for (let j = 0; j < 7; j++) {
                const isBorder = i === 0 || i === 6 || j === 0 || j === 6;
                const isCenter = i >= 2 && i <= 4 && j >= 2 && j <= 4;
                matrix[x + i][y + j] = isBorder || isCenter;
            }
        }
    }

    _encodeText(text) {
        // Detectar modo de codificación (Byte/Alfanumérico)
        const hasNonAlphanumeric = /[^0-9A-Z $%*+\-./:]/.test(text);
        const modeIndicator = hasNonAlphanumeric ? '0100' : '0010';

    _calculateVersion(text, isByte) {
        const capacities = {
            'L': [41, 77, 127, 187, 255, 322, 370, 461, 552, 652],
            'M': [34, 63, 101, 149, 202, 255, 293, 365, 432, 513],
            'Q': [27, 48, 77, 111, 144, 178, 207, 259, 312, 364],
            'H': [17, 34, 58, 82, 106, 139, 154, 202, 235, 288]
        };
        const mode = isByte ? 'Byte' : 'Alphanumeric';
        const length = text.length;
        const maxVer = capacities[this.options.errorCorrection].findIndex(cap => cap > length);
        return Math.min(Math.max(maxVer + 1, 1), 10);
    },

    _addAlignmentPatterns(matrix, version) {
        const positions = [
            [], [18], [22], [26], [30], [34], [6, 22, 38], [6, 24, 42], 
            [6, 26, 46], [6, 28, 50], [6, 30, 54]
        ][version - 1];
        
        positions.forEach(x => {
            positions.forEach(y => {
                if (!matrix[x][y]) {
                    for(let i = -2; i <= 2; i++) {
                        for(let j = -2; j <= 2; j++) {
                            matrix[x+i][y+j] = Math.max(Math.abs(i), Math.abs(j)) !== 1;
                        }
                    }
                }
            });
        });
    },

    _splitIntoCodewords(bitStr) {
        const codewords = [];
        for(let i = 0; i < bitStr.length; i += 8) {
            const byte = bitStr.substr(i, 8).padEnd(8, '0');
            codewords.push(parseInt(byte, 2));
        }
        return codewords;
    },

    _generateReedSolomon(dataCodewords, version) {
        const eccInfo = [
            [19,7], [34,10], [55,15], [80,20], [108,26], [136,18], 
            [156,20], [194,24], [232,30], [274,18]
        ][version - 1];
        
        const generator = this._rsGenerator(eccInfo[1]);
        const ecc = new Array(eccInfo[1]).fill(0);
        
        dataCodewords.forEach(coeff => {
            const term = coeff ^ ecc[0];
            ecc.shift();
            ecc.push(0);
            generator.forEach((gen, i) => ecc[i] ^= this._galoisMultiply(term, gen));
        });
        return ecc.map(b => b.toString(2).padStart(8, '0')).join('');
    },

    _rsGenerator(degree) {
        let gen = [1];
        for(let i = 0; i < degree; i++) {
            gen = this._galoisMultiplyPoly(gen, [1, this._galoisExp(i)]);
        }
        return gen;
    },

    _galoisMultiply(a, b) {
        return a === 0 || b === 0 ? 0 : this._galoisExp(this._galoisLog[a] + this._galoisLog[b]);
    },

    _galoisExp(n) {
        return this._galoisField[(n % 255 + 255) % 255];
    },

    _galoisLog = new Array(256).fill(0).map((_,i) => i ? this._galoisLog[i] = Math.log(i)/Math.LN2 : 0),
    _galoisField = (() => {
        let field = [1];
        for(let i = 1; i < 256; i++) {
            field[i] = field[i-1] << 1;
            if(field[i] & 0x100) field[i] ^= 0x11D;
        }
        return field;
    })()
        
        // Calcular capacidad según versión y nivel de corrección
        const version = this._calculateVersion(text, hasNonAlphanumeric);
        const charCountBits = version < 10 ? 8 : 16;
        
        let bitString = modeIndicator + 
            text.length.toString(2).padStart(charCountBits, '0');

        const encodingTable = {
            '0':0, '1':1, '2':2, '3':3, '4':4, '5':5, '6':6, '7':7, '8':8, '9':9,
            'A':10, 'B':11, 'C':12, 'D':13, 'E':14, 'F':15, 'G':16, 'H':17, 'I':18,
            'J':19, 'K':20, 'L':21, 'M':22, 'N':23, 'O':24, 'P':25, 'Q':26, 'R':27,
            'S':28, 'T':29, 'U':30, 'V':31, 'W':32, 'X':33, 'Y':34, 'Z':35,
            ' ' :36, '$':37, '%':38, '*':39, '+':40, '-':41, '.':42, '/':43, ':':44
        };

        for (let i = 0; i < text.length; i += 2) {
            const c1 = encodingTable[text[i]];
            const c2 = encodingTable[text[i + 1]] || 0;
            const value = c1 * 45 + c2;
            bitString += value.toString(2).padStart(11, '0');
        }

        // Codificación Reed-Solomon
        const dataCodewords = this._splitIntoCodewords(bitString);
        const ecc = this._generateReedSolomon(dataCodewords, version);
        return [...dataCodewords, ...ecc].join('');
    }

    // Método para obtener la URL del QR como imagen
    toDataURL() {
        if(this.svgMode) {
            const serializer = new XMLSerializer();
            const svgStr = serializer.serializeToString(this.svg);
            return 'data:image/svg+xml;base64,' + btoa(svgStr);
        }
        return this.canvas.toDataURL('image/png');
    }
}

// Función de ayuda para crear un QR y obtener su URL
function generateQRCodeURL(text, options = {}) {
    const element = options.renderSVG ? document.createElementNS('http://www.w3.org/2000/svg', 'svg') : document.createElement('canvas');
    const qr = new QRCode(element, options);
    qr.generate(text);
    return qr.toDataURL();
}