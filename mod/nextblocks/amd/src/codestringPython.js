/**
 *
 * @module      mod_nextblocks/codestringPython
 * @copyright   2025 Rui Correia<rjr.correia@campus.fct.unl.pt>
 * @copyright   based on work by 2024 Duarte Pereira<dg.pereira@campus.fct.unl.pt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {

    class CodeStringPython {
        #codeString;
        #userFunctionLinesCount;

        static #auxFunctions =
            `
const runningTests = false;
const testInputs = [];
const MAX_ITERATIONS = 10000;
var nextInput = 0;
var loopIterations = 0;
var error = "";

customPrintln = function(string) {
  outputString += string + '\\n';
  updateTerminal();
  return string;
};

updateTerminal = function(){
  if (runningTests) return;
  const outputDiv = document.getElementById('output-div');
  if (outputDiv) {
    outputDiv.innerHTML = "";
    const pre = document.createElement('pre');
    pre.style.whiteSpace = 'pre-wrap'; 
    pre.textContent = outputString; 
    outputDiv.appendChild(pre);
  }
}

text_to_number = function(string){
    let result = parseFloat(string);
    if (isNaN(result)){
        if (error.length == 0) error = "Error: Cannot convert "  + string + " to a number.";
        return 0;
    }
    return result;
}

async function input(promptText) {
  if(error.length > 0) return "";
  if(runningTests){
    if(nextInput++ >= testInputs.length) return "";
    return testInputs[nextInput-1];
  }
  const terminal = document.getElementById('output-div')
  customPrintln(promptText);
  const inputBox = document.createElement('input');
  inputBox.setAttribute('id', 'input-box-inline');
  
  inputBox.style.backgroundColor = "black";
  inputBox.style.color="white";
  
  inputBox.style.display = 'inline-block';
  inputBox.style.marginLeft = '5px';
  inputBox.style.position = 'relative';
  inputBox.style.top = '-40px'; 
  inputBox.style.left = ''+(promptText.length*7.5)+'px';
  terminal.appendChild(inputBox);
  inputBox.focus();

  return new Promise((resolve) => {
    const handleKeyDown = (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        inputBox.removeEventListener('keydown', handleKeyDown);
        const answer = inputBox.value.trim();
        terminal.removeChild(inputBox);
        resolve(answer);
      }
    };
    inputBox.addEventListener('keydown', handleKeyDown);
  });
}




`;
        static #codeEnding = `})();
        if(nextInput > testInputs.length) return "Error: Too many inputs";
        if(error.length > 0) return error;
        
        return outputString;
})();
`;
        constructor(codeString) {
            if (arguments.length > 0) {
                this.#codeString += codeString;
                this.addVariable('outputString', '""');
            } else {
                this.#codeString = '';
                this.addVariable('outputString', '""');


            }
            this.#userFunctionLinesCount = 0;
        }

        getCompleteCodeString() {
            return this.#codeString;
        }

        getPrintableCodeString() {
            // Split code by unescaped line breaks (code might have escaped line breaks)
            const codeLines = this.#codeString
                .replaceAll("customPrintln", "print")
                .replaceAll("await ", "")
                .replaceAll("text_to_number", "float")
                .split(/(?<!\\)\n/);

            // Add lines from start block
            const startIndex = codeLines.findIndex(line => line.includes('(async () => {')) + 1;
            const endIndex = codeLines.findIndex(line => line.includes('})();'));
            const startCodeLines = codeLines.slice(startIndex, endIndex);

            return startCodeLines.join('\n');
        }

        getSubmittableCodeString() {
            // Replace return outputString; with process.stdout.write(outputString);
            let lastIndex = this.#codeString.lastIndexOf('return outputString;');
            return this.#codeString.substring(0, lastIndex) + 'process.stdout.write(outputString);' +
                this.#codeString.substring(lastIndex + 'return outputString;'.length);
        }

        addVariable(variableName, variableValue) {
            // Check if variableName is a valid variable name
            const regex = /^[a-zA-Z_][a-zA-Z0-9_]*$/;
            if (!regex.test(variableName)) {
                throw new Error('Invalid variable name');
            }
            this.#codeString += 'let ' + variableName + ' = ' + variableValue + ';\n';
            return this.#codeString;
        }
        addLine(line) {
            // Check if line does not have line break
            if (line.includes('\n')) {
                throw new Error('Invalid line');
            }
            this.#codeString += line + '\n';
            return this.#codeString;
        }

        addEnding() {
            this.#codeString += CodeStringPython.#codeEnding;
            return this.#codeString;
        }

        addAuxFunctions(inputFuncDecs) {
            const auxFunctions = inputFuncDecs + CodeStringPython.#auxFunctions;
            this.#codeString += auxFunctions;
            return this.#codeString;
        }

        addMainCode(codeString) {
            this.#codeString += codeString;
            return this.#codeString;
        }

        addFunction(functionCode) {
            // Update user function lines count
            const regex = /(?<!\\)\n/g;
            const functionLinesCount = (functionCode.match(regex) || []).length;
            this.#userFunctionLinesCount += functionLinesCount;

            this.#codeString = functionCode + this.#codeString;
            return this.#codeString;
        }
    }
    return CodeStringPython;
});
