/**
 *
 * @module      mod_nextblocks/codeenv
 * @copyright   2025 Rui Correia<rjr.correia@campus.fct.unl.pt>
 * @copyright   based on work by 2024 Duarte Pereira<dg.pereira@campus.fct.unl.pt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *  */

// eslint-disable-next-line max-len
define(['mod_nextblocks/lib', 'mod_nextblocks/repository', 'mod_nextblocks/chat', 'mod_nextblocks/block_categories', 'core/str', 'core/ajax'],
    function(lib, repository, chat, categories, str, ajax) {

        /* globals Blockly */
        let toolbox = {
            'kind': 'categoryToolbox',
            'readOnly': true,
            'contents': [
                {
                    'kind': 'toolboxlabel',
                    'name': 'NextBlocks',
                    'colour': 'darkslategrey'
                },
                {
                    'kind': 'category',
                    'name': 'Logic',
                    'colour': '5b80a5',
                    "cssConfig": {
                        'icon': 'customIcon fa fa-cog',
                    },
                    'contents': [
                        {
                            'kind': 'block',
                            'type': 'controls_if',
                        },
                        {
                            'kind': 'block',
                            'type': 'logic_compare',
                        },
                        {
                            'kind': 'block',
                            'type': 'logic_negate',
                        },
                        {
                            'kind': 'block',
                            'type': 'logic_operation',
                        },
                        {
                            'kind': 'block',
                            'type': 'logic_boolean',
                        },
                        {
                            'kind': 'block',
                            'type': 'logic_null',
                        },
                        {
                            'kind': 'block',
                            'type': 'logic_ternary',
                        }
                    ],
                },
                {
                    'kind': 'category',
                    'name': 'Loops',
                    'colour': '5ba580',
                    "cssConfig": {
                        'icon': 'customIcon fa-solid fa-sync',
                    },
                    'contents': [
                        {
                            'kind': 'block',
                            'type': 'controls_repeat_ext',
                        },
                        {
                            'kind': 'block',
                            'type': 'controls_whileUntil',
                        },
                        {
                            'kind': 'block',
                            'type': 'controls_for',
                        },
                        {
                            'kind': 'block',
                            'type': 'controls_forEach',
                        },
                        {
                            'kind': 'block',
                            'type': 'controls_flow_statements',
                        }
                    ],
                },
                {
                    'kind': 'category',
                    'name': 'Math',
                    'colour': '5b67a5',
                    "cssConfig": {
                        'icon': 'customIcon fa-solid fa-plus-minus',
                    },
                    'contents': [
                        {
                            'kind': 'block',
                            'type': 'math_number',
                        },
                        {
                            'kind': 'block',
                            'type': 'math_arithmetic',
                        },
                        {
                            'kind': 'block',
                            'type': 'math_single',
                        },
                        {
                            'kind': 'block',
                            'type': 'math_trig',
                        },
                        {
                            'kind': 'block',
                            'type': 'math_constant',
                        },
                        {
                            'kind': 'block',
                            'type': 'math_number_property',
                        },
                        {
                            'kind': 'block',
                            'type': 'math_round',
                        },
                        {
                            'kind': 'block',
                            'type': 'math_on_list',
                        },
                        {
                            'kind': 'block',
                            'type': 'math_modulo',
                        },
                        {
                            'kind': 'block',
                            'type': 'math_constrain',
                        },
                        {
                            'kind': 'block',
                            'type': 'math_random_int',
                        },
                        {
                            'kind': 'block',
                            'type': 'math_random_float',
                        },
                        {
                            'kind': 'block',
                            'type': 'math_atan2',
                        },
                        {
                            'kind': 'block',
                            'type': 'text_to_number',
                        },

                    ],
                },
                {
                    'kind': 'category',
                    'name': 'Text',
                    'colour': '5ba58c',
                    "cssConfig": {
                        'icon': 'customIcon fa-solid fa-font',
                    },
                    'contents': [
                        {
                            'kind': 'block',
                            'type': 'text',
                        },
                        {
                            'kind': 'block',
                            'type': 'text_multiline',
                        },
                        {
                            'kind': 'block',
                            'type': 'text_join',
                        },
                        {
                            'kind': 'block',
                            'type': 'text_append',
                        },
                        {
                            'kind': 'block',
                            'type': 'text_length',
                        },
                        {
                            'kind': 'block',
                            'type': 'text_isEmpty',
                        },
                        {
                            'kind': 'block',
                            'type': 'text_indexOf',
                        },
                        {
                            'kind': 'block',
                            'type': 'text_charAt',
                        },
                        {
                            'kind': 'block',
                            'type': 'text_getSubstring',
                        },
                        {
                            'kind': 'block',
                            'type': 'text_changeCase',
                        },
                        {
                            'kind': 'block',
                            'type': 'text_trim',
                        },
                        {
                            'kind': 'block',
                            'type': 'text_count',
                        },
                        {
                            'kind': 'block',
                            'type': 'text_replace',
                        },
                        {
                            'kind': 'block',
                            'type': 'text_reverse',
                        },
                    ],
                },
                {
                    'kind': 'category',
                    'name': 'Lists',
                    'colour': '5b80a5',
                    "cssConfig": {
                        'icon': 'customIcon fa-solid fa-list',
                    },
                    'contents': [
                        {
                            'kind': 'block',
                            'type': 'lists_create_with',
                        },
                        {
                            'kind': 'block',
                            'type': 'lists_repeat',
                        },
                        {
                            'kind': 'block',
                            'type': 'lists_length',
                        },
                        {
                            'kind': 'block',
                            'type': 'lists_isEmpty',
                        },
                        {
                            'kind': 'block',
                            'type': 'lists_indexOf',
                        },
                        {
                            'kind': 'block',
                            'type': 'lists_getIndex',
                        },
                        {
                            'kind': 'block',
                            'type': 'lists_setIndex',
                        },
                        {
                            'kind': 'block',
                            'type': 'lists_getSublist',
                        },
                        {
                            'kind': 'block',
                            'type': 'lists_split',
                        },
                        {
                            'kind': 'block',
                            'type': 'lists_sort',
                        },
                        {
                            'kind': 'block',
                            'type': 'lists_reverse',
                        }
                    ],
                },
                {
                    'kind': 'category',
                    'name': 'Input/Output',
                    'colour': '4682B4',
                    "cssConfig": {
                        'icon': 'customIcon fa-solid fa-terminal',
                    },
                    'contents': [
                        {
                            'kind': 'block',
                            'type': 'text_print',
                        },
                        {
                            'kind': 'block',
                            'type': 'text_ask',
                        },
                    ],
                },
                {
                    'kind': 'category',
                    'name': 'Variables',
                    'colour': 'a55b80',
                    "cssConfig": {
                        'icon': 'customIcon fa-solid fa-clipboard-list',
                    },
                    'custom': 'VARIABLE',
                },
                {
                    'kind': 'category',
                    'name': 'Functions',
                    'colour': '995ba5',
                    "cssConfig": {
                        'icon': 'customIcon fa-solid fa-code',
                    },
                    'custom': 'PROCEDURE',
                },
            ],
        };

        let nextblocksWorkspace;

    /**
     * @param {CodeString} code The Javascript code to be run
     * Runs the code and displays the output in the output div
     */
    async function runCode(code) {
        const outputDiv = document.getElementById('output-div');
        outputDiv.classList.remove('tests-active');
        var codeString = code.getCompleteCodeString();

        codeString = lib.errorPrevention(codeString);

        const output = await lib.silentRunCode(codeString);

        // Replace newlines with <br /> so that they are displayed correctly
        const outputHTML = String(output).replace(/\n/g, "<br />");
        // Wrap the output in a div with max-height and overflow-y: auto to make it scrollable if too long (multiline input)
        if (output.includes("Error")) {
            // eslint-disable-next-line max-len
            outputDiv.innerHTML = `<div style="max-height: 100%; overflow-y: auto; color: red !important; background-color: black;"><pre>${outputHTML}</pre></div>`;
        } else {
            // eslint-disable-next-line max-len
            outputDiv.innerHTML = `<div style="max-height: 100%; overflow-y: auto; color: white !important; background-color: black;"><pre>${outputHTML}</pre></div>`;
        }
    }

    /**
     * Saves the current state of the workspace to the database, for later retrieval and display
     * By default, the workspace is saved to the currently logged-in user's entry in the database
     * If a teacher is adding a comment to a student's submission, the student's id is passed as an argument,
     * because in that case the workspace should be saved to the student's entry in the database, not to the teacher's.
     * @param {bool} isTeacherReport whether the current page is a teacher report. If so, we need to pass the student's id,
     * because PHP will not be able to get it from the user api, as the logged-in user will be the teacher
     */
    const saveState = (isTeacherReport) => {
        const state = Blockly.serialization.workspaces.save(nextblocksWorkspace);
        const stateB64 = btoa(JSON.stringify(state));
        const cmid = getCMID();

        if (isTeacherReport) {
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            const userId = urlParams.get('userid');

            repository.saveWorkspace(cmid, stateB64, userId);
        } else {
            repository.saveWorkspace(cmid, stateB64);
        }
    };

    const submitWorkspace = async(inputFuncDecs) => {
        const codeString = lib.getWorkspaceCode(nextblocksWorkspace, inputFuncDecs).getTestableCodeString();
        const state = Blockly.serialization.workspaces.save(nextblocksWorkspace);
        const stateB64 = btoa(JSON.stringify(state));
        const cmid = getCMID();
        repository.submitWorkspace(cmid, stateB64, codeString);

        const delay = ms => new Promise(resolve => setTimeout(resolve, ms));
        await delay(1000);

        location.reload();

        str.get_string('submitsuccess', 'mod_nextblocks').then(function(text) {
            alert(text);
        });
    };

    /**
     * @param {any[]} results The results of the tests
     * @param {{}} tests The tests that were run
     * Displays the results of the tests in the output div
     */
    async function displayTestResults(results, tests) {
        const testResultsDiv = document.getElementById('output-div');
        testResultsDiv.classList.add('tests-active');
        testResultsDiv.innerHTML = "";
        testResultsDiv.innerHTML = await lib.testsAccordion(results, tests);
    }

    // Makes background of image blue if it is not blue, and vice versa
    const changeImageBackground = function(img) {
        // Change background of all other images to secondary
        const imgs = document.getElementsByClassName("emoji-img");
        Array.from(imgs).forEach((otherImg) => {
            if (otherImg !== img) {
                otherImg.classList.remove("bg-primary");
                otherImg.classList.add("bg-secondary");
            }
        });

        // Toggle background of clicked image
        if (img.classList.contains("bg-primary")) {
            img.classList.remove("bg-primary");
            img.classList.add("bg-secondary");
        } else {
            img.classList.remove("bg-secondary");
            img.classList.add("bg-primary");
        }
    };

    /**
     * @param {{}} tests The tests to be run
     * @param {WorkspaceSvg} workspace The workspace to get the code from
     * @param {string} inputFuncDecs
     * @param {number} lastUserReaction The type of reaction the current user last submitted
     * @param {boolean} isTeacherReport Whether the report to be displayed is a teacher report
     * @param {{}} blockLimits block limits set by the teacher
     */
    function setupButtons(tests, workspace, inputFuncDecs, lastUserReaction, isTeacherReport, blockLimits) {
        // Listen for clicks on the run button
        const runButton = document.getElementById('runButton');
        runButton.addEventListener('click', function() {
            const code = lib.getWorkspaceCode(workspace, inputFuncDecs);
            // Lib.replaceCode(code);
            runCode(code);
        });

        if (tests !== null) {
            // Listen for clicks on the run tests button
            const runTestsButton = document.getElementById('runTestsButton');
            runTestsButton.addEventListener('click', async() => { // Needs anonymous function wrap to pass argument
                const code = lib.getWorkspaceCode(workspace, inputFuncDecs).getCompleteCodeString();
                lib.runTests(code, tests).then((results) => {
                    displayTestResults(results, tests);
                });
            });
        }

        // Listen for clicks on the save button
        const saveButton = document.getElementById('saveButton');
        saveButton.addEventListener('click', () => {
            saveState(isTeacherReport);
        });

        // Listen for clicks on the submit button, if it exists (doesn't exist in report pages)
        const submitButton = document.getElementById('submitButton');
        if (submitButton !== null) {
            submitButton.addEventListener('click', () => {
                submitWorkspace(inputFuncDecs);
            });
        }

        // Convert the lastUserReaction to a string
        let lastUserReactionString = "";
        if (lastUserReaction === 1) {
            lastUserReactionString = "easy";
        } else if (lastUserReaction === 2) {
            lastUserReactionString = "medium";
        } else if (lastUserReaction === 3) {
            lastUserReactionString = "hard";
        }

        const imgs = document.getElementsByClassName("emoji-img");
        Array.from(imgs).forEach((img) => {
            let imageType = '';
            if (img.src.includes("easy")) {
                imageType = "easy";
            } else if (img.src.includes("think")) {
                imageType = "medium";
            } else if (img.src.includes("hard")) {
                imageType = "hard";
            }

            // Start with one image selected if the user has already reacted in a previous session
            if (lastUserReactionString === imageType) {
                changeImageBackground(img);
            }

            // Only listen for clicks on the images if page is not a teacher report
            if (!isTeacherReport) {
                img.addEventListener("click", () => {
                    // Submit reaction, and wait for response with new reaction counts
                    const newReactionsPromise = repository.submitReaction(getCMID(), imageType);
                    newReactionsPromise.then((newReactions) => {
                        updatePercentages(newReactions.reactionseasy, newReactions.reactionsmedium, newReactions.reactionshard);
                        changeImageBackground(img);
                    });
                });
            }
        });

        const textCodeButton = document.getElementById('showCodeButton');
        let codeVisible = false; // Variable to track the visibility state
        let overlayDiv;
        let limitsDiv;

        textCodeButton.addEventListener('click', () => {
            const blocklyArea = document.getElementById('blocklyArea');
            const paddingLeft = parseInt(window.getComputedStyle(blocklyArea).getPropertyValue('padding-left'));
            const paddingRight = parseInt(window.getComputedStyle(blocklyArea).getPropertyValue('padding-right'));

            if (codeVisible) {
                overlayDiv.style.display = 'none';
                codeVisible = false;
            } else {
                if (!overlayDiv) {
                    overlayDiv = document.createElement('div');
                    overlayDiv.style.position = 'absolute';
                    overlayDiv.style.top = '0';
                    overlayDiv.style.left = `${paddingLeft}px`;
                    overlayDiv.style.width = `calc(100% - ${paddingLeft + paddingRight}px)`;
                    overlayDiv.style.height = '100%';
                    overlayDiv.style.backgroundColor = 'white';
                    overlayDiv.style.border = '1px solid #ddd';
                    overlayDiv.style.padding = '10px';
                    overlayDiv.style.fontFamily = '"Lucida Console", "Courier New", monospace';
                    overlayDiv.style.zIndex = '1000';
                    blocklyArea.appendChild(overlayDiv);

                    const headerDiv = document.createElement('div');
                    headerDiv.id = 'langContainer';
                    headerDiv.style.position = 'absolute';
                    headerDiv.style.top = '5px';
                    headerDiv.style.left = '5px';
                    headerDiv.style.zIndex = '1100';
                    overlayDiv.appendChild(headerDiv);

                    const jsButton = document.createElement('button');
                    jsButton.id = 'jsButton';
                    jsButton.textContent = 'JavaScript';
                    jsButton.style.marginRight = '5px';
                    headerDiv.appendChild(jsButton);

                    const pyButton = document.createElement('button');
                    pyButton.id = 'pyButton';
                    pyButton.textContent = 'Python';
                    headerDiv.appendChild(pyButton);

                    const codeContentDiv = document.createElement('div');
                    codeContentDiv.id = 'codeContent';
                    codeContentDiv.style.marginTop = '40px';
                    overlayDiv.appendChild(codeContentDiv);


                    jsButton.addEventListener('click', () => {
                        updateOverlayCode('javascript');
                        jsButton.classList.add('selected-button');
                        pyButton.classList.remove('selected-button');
                    });

                    pyButton.addEventListener('click', () => {
                        updateOverlayCode('python');
                        pyButton.classList.add('selected-button');
                        jsButton.classList.remove('selected-button');
                    });

                    jsButton.classList.add('selected-button');
                }

                updateOverlayCode('javascript');
                document.querySelectorAll('.blocklyHtmlInput').forEach(input => {
                    input.style.display = 'none';
                });
                overlayDiv.style.display = 'block';
                codeVisible = true;
            }
        });

        const blockLimitsButton = document.getElementById('showBlockLimits');
        let blockLimitsVisible = false;
        let builtinCategories;

        categories.init().then(function(categoriesList) {
            builtinCategories = categoriesList;
        });

        blockLimitsButton.addEventListener('click', () => {
            if (blockLimitsVisible) {
                document.body.removeChild(limitsDiv);
                blockLimitsVisible = false;
            } else {
                limitsDiv = document.createElement('div');
                limitsDiv.style.position = 'fixed';
                limitsDiv.style.top = '50px';
                limitsDiv.style.left = '50px';
                limitsDiv.style.right = '50px';
                limitsDiv.style.bottom = '50px';
                limitsDiv.style.backgroundColor = 'white';
                limitsDiv.style.border = '1px solid #ddd';
                limitsDiv.style.padding = '10px';
                limitsDiv.style.fontFamily = '"Lucida Console", "Courier New", monospace';
                limitsDiv.style.zIndex = '10000';
                limitsDiv.style.display = 'flex';
                limitsDiv.style.flexDirection = 'column';
                limitsDiv.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';

                const headerDiv = document.createElement('div');
                headerDiv.style.flexShrink = '0';
                headerDiv.style.padding = '0 10px';
                limitsDiv.appendChild(headerDiv);

                const heading = document.createElement('h3');
                heading.textContent = 'Block Limits';
                heading.style.marginBottom = '1em';
                heading.style.borderBottom = '1px solid #dee2e6';
                heading.style.paddingBottom = '0.5em';
                headerDiv.appendChild(heading);

                const scrollableDiv = document.createElement('div');
                scrollableDiv.style.overflowY = 'auto';
                scrollableDiv.style.flexGrow = '1';
                scrollableDiv.style.minHeight = '0';
                limitsDiv.appendChild(scrollableDiv);

                const contentContainer = document.createElement('div');
                contentContainer.id = 'block-limits-content';
                contentContainer.style.padding = '10px';
                scrollableDiv.appendChild(contentContainer);

                for (const [catName, blocks] of Object.entries(builtinCategories)) {
                    const details = document.createElement('details');
                    details.style.marginBottom = '0.8em';

                    const summary = document.createElement('summary');
                    summary.style.fontWeight = 'bold';
                    summary.style.cursor = 'pointer';
                    summary.style.padding = '5px 0';
                    summary.textContent = catName;
                    details.appendChild(summary);

                    for (const [blockType, blockLabel] of Object.entries(blocks)) {
                        const blockRow = document.createElement('div');
                        blockRow.style.display = 'flex';
                        blockRow.style.justifyContent = 'space-between';
                        blockRow.style.padding = '0.5em 1em';
                        blockRow.style.borderBottom = '1px solid #eee';

                        const labelSpan = document.createElement('span');
                        labelSpan.textContent = blockLabel;
                        blockRow.appendChild(labelSpan);

                        const valueSpan = document.createElement('span');
                        valueSpan.style.fontWeight = 'bold';
                        valueSpan.style.minWidth = '80px';
                        valueSpan.style.textAlign = 'right';

                        if (blockLimits[blockType] !== undefined) {
                            valueSpan.textContent = blockLimits[blockType];
                            valueSpan.style.color = '#1e88e5';
                        } else {
                            valueSpan.textContent = '∞';
                            valueSpan.style.color = '#43a047';
                        }

                        blockRow.appendChild(valueSpan);
                        details.appendChild(blockRow);
                    }
                    contentContainer.appendChild(details);
                }

                const closeButton = document.createElement('button');
                closeButton.textContent = 'Close';
                closeButton.style.alignSelf = 'flex-end';
                closeButton.style.marginTop = '10px';
                closeButton.style.padding = '6px 15px';
                closeButton.addEventListener('click', () => {
                    document.body.removeChild(limitsDiv);
                    blockLimitsVisible = false;
                });
                headerDiv.appendChild(closeButton);

                document.body.appendChild(limitsDiv);
                blockLimitsVisible = true;
            }
        });
        /**
         * A helper function to update the code in the overlay according to the chosen language.
         * @param {string} lang The chosen language.
         */
        function updateOverlayCode(lang) {
            let codeString = '';
            if (lang == 'python') {
                codeString = lib.formatPythonCodeHTML(lib.getWorkspaceCodePython(workspace, inputFuncDecs));
            } else {
                codeString = lib.formatCodeHTML(lib.getWorkspaceCode(workspace, inputFuncDecs));
            }
            codeString = codeString.replace(/\n/g, "<br />");
            const codeContentDiv = document.getElementById('codeContent');
            if (codeContentDiv) {
                codeContentDiv.innerHTML = codeString;
            }
        }
    }

    Blockly.JavaScript.forBlock.text_print = function(block, generator) {
        return (
            "customPrintln(" +
            (generator.valueToCode(
                block,
                "TEXT",
                Blockly.JavaScript.ORDER_NONE
            ) || "''") +
            ");\n"
        );
    };

    Blockly.JavaScript.forBlock.text_ask = function(block, generator) {
        const question = (generator.valueToCode(
            block,
            'TEXT',
            Blockly.JavaScript.ORDER_NONE
        ) || "''");
        let code = "await input(" + question + ")";
        return [code, Blockly.JavaScript.ORDER_NONE];
    };

    Blockly.Python.forBlock.text_ask = function(block, generator) {
        const question = (generator.valueToCode(
            block,
            'TEXT',
            Blockly.Python.ORDER_NONE
        ) || "''");
        let code = "input(" + question + ")";
        return [code, Blockly.Python.ORDER_NONE];
    };

    Blockly.Blocks['text_ask'] = {
        init: function() {
            this.appendValueInput("TEXT")
                .setCheck(null)
                .appendField("input");
            this.setOutput(true, "String");
            this.setColour(160);
            this.setTooltip("");
            this.setHelpUrl("");
        }
    };

    Blockly.JavaScript.forBlock.text_to_number = function(block, generator) {
        const prompt = (generator.valueToCode(
            block,
            'TEXT',
            Blockly.JavaScript.ORDER_NONE
        ) || "''").trim();
        let code = "text_to_number(" + prompt + ")";
        return [code, Blockly.JavaScript.ORDER_NONE];
    };

    Blockly.Python.forBlock.text_to_number = function(block, generator) {
        const prompt = (generator.valueToCode(
            block,
            'TEXT',
            Blockly.Python.ORDER_NONE
        ) || "''").trim();
        let code = "text_to_number(" + prompt + ")";
        return [code, Blockly.Python.ORDER_NONE];
    };

    Blockly.Blocks['text_to_number'] = {
        init: function() {
            this.appendValueInput("TEXT")
                .setCheck(null)
                .appendField("text to number");
            this.setOutput(true, "Number");
            this.setColour("#5b67a5");
            this.setTooltip("");
            this.setHelpUrl("");
        }
    };

    Blockly.Blocks.number_input = {
        init: function() {
            this.appendDummyInput()
                .appendField("number input")
                .appendField(new Blockly.FieldNumber(0), "number_input");
            this.setOutput(true, "Number");
            this.setColour(180);
            this.setTooltip("");
            this.setHelpUrl("");
        }
    };

    Blockly.Blocks.text_input = {
        init: function() {
            this.appendDummyInput()
                .appendField("text input:")
                .appendField(new Blockly.FieldTextInput('text'),
                    'text_input');
            this.setOutput(true, "String");
            this.setColour(180);
            this.setTooltip("");
            this.setHelpUrl("");
        }
    };

    Blockly.Blocks.text_multiline_input = {
        init: function() {
            this.appendDummyInput()
                .appendField("multiline text input:")
                .appendField(new Blockly.FieldMultilineInput('multiline \n text'),
                    'text_input');
            this.setOutput(true, "String");
            this.setColour(180);
            this.setTooltip("");
            this.setHelpUrl("");
        }
    };

    Blockly.Blocks.start = {
        init: function() {
            this.appendDummyInput()
                .appendField("start");
            this.setNextStatement(true, null);
            this.setColour(60);
            this.setTooltip("");
            this.setHelpUrl("");
            this.setDeletable(false);
        }
    };

    // eslint-disable-next-line no-unused-vars
    Blockly.JavaScript.forBlock.start = function(block, generator) {
        // Get all blocks attached to this block
        let code = '';
        return code;
    };

    // eslint-disable-next-line no-unused-vars
    Blockly.JavaScript.forBlock.number_input = function(block, generator) {
        const number = block.getFieldValue('number_input');
        let code = 'input(' + number + ')';
        return [code, Blockly.JavaScript.ORDER_NONE];
    };

    // eslint-disable-next-line no-unused-vars
    Blockly.JavaScript.forBlock.text_input = function(block, generator) {
        const text = block.getFieldValue('text_input');
        let code = 'input("' + text + '")';
        return [code, Blockly.JavaScript.ORDER_NONE];
    };

    // eslint-disable-next-line no-unused-vars
    Blockly.JavaScript.forBlock.text_multiline_input = function(block, generator) {
        const text = block.getFieldValue('text_input');
        let code = "input(`" + text + "`)";
        return [code, Blockly.JavaScript.ORDER_NONE];
    };

    class ToolboxLabel extends Blockly.ToolboxItem {
        constructor(toolboxItemDef, parentToolbox) {
            super(toolboxItemDef, parentToolbox);
        }

        /** @override */
        init() {
            // Create the label.
            this.label = document.createElement('label');

            // Set the name.
            this.label.textContent = this.toolboxItemDef_.name;
            // Set the color.
            this.label.style.color = this.toolboxItemDef_.colour;
        }

        /** @override */
        getDiv() {
            return this.label;
        }
    }

    class CustomCategory extends Blockly.ToolboxCategory {
        /**
         * Constructor for a custom category.
         * @override
         */
        constructor(categoryDef, toolbox, optParent) {
            super(categoryDef, toolbox, optParent);
        }

        /** @override */
        addColourBorder_(colour) {
            this.rowDiv_.style.backgroundColor = colour;
        }

        /** @override */
        setSelected(isSelected) {
            // We do not store the label span on the category, so use getElementsByClassName.
            var labelDom = this.rowDiv_.getElementsByClassName('blocklyTreeLabel')[0];
            if (isSelected) {
                // Change the background color of the div to white.
                this.rowDiv_.style.backgroundColor = 'white';
                // Set the colour of the text to the colour of the category.
                labelDom.style.color = this.colour_;
                this.iconDom_.style.color = this.colour_;
            } else {
                // Set the background back to the original colour.
                this.rowDiv_.style.backgroundColor = this.colour_;
                // Set the text back to white.
                labelDom.style.color = 'white';
                this.iconDom_.style.color = 'white';
            }
            // This is used for accessibility purposes.
            Blockly.utils.aria.setState(/** @type {!Element} */ (this.htmlDiv_),
                Blockly.utils.aria.State.SELECTED, isSelected);
        }
    }

    // Remove native blockly comments
    Blockly.ContextMenuRegistry.registry.unregister('blockComment');

    str.get_string('viewaddcomments', 'mod_nextblocks').then(function(text) {
        Blockly.ContextMenuRegistry.registry.register({
            displayText: function () {
                return text;
            },
            preconditionFn: function () {
                return 'enabled';
            },
            scopeType: Blockly.ContextMenuRegistry.ScopeType.BLOCK,
            id: 'custom_comments',
            weight: 100,
            callback: function (scope) {
                openCommentDialog(scope.block, getCMID(), globalReportType);
            }
        });
    });

    let currentBlockId = null;
    let currentDialog = null;
    let globalReportType = 0;

    /**
     * Opens the comments from a specific block
     * @param {object} block the block with the comments
     * @param {Number} cmid context module id
     * @param {Number} reportType 0 if student, 1 if teacher
     */
    function openCommentDialog(block, cmid, reportType) {
        if (currentDialog) {
            currentDialog.remove();
            currentDialog = null;
        }

        currentBlockId = block.id;

        currentDialog = document.createElement('div');
        currentDialog.className = 'custom-comment-dialog';
        currentDialog.style = `position: absolute; z-index: 1000; background: white; 
                           border: 1px solid #ddd; padding: 15px; width: 350px;`;

        const commentsContainer = document.createElement('div');
        commentsContainer.id = 'comments-container';
        commentsContainer.style.maxHeight = '200px';
        commentsContainer.style.overflowY = 'auto';
        currentDialog.appendChild(commentsContainer);

        const form = document.createElement('div');
        form.innerHTML = `
        <textarea class="new-comment-text" rows="3" style="width:100%; margin:10px 0"></textarea>
        <button class="add-comment-btn">Add Comment</button>
    `;
        currentDialog.appendChild(form);

        const closeBtn = document.createElement('button');
        closeBtn.innerText = 'Close';
        closeBtn.onclick = () => {
            currentDialog.remove();
            currentDialog = null;
        };
        currentDialog.appendChild(closeBtn);

        document.body.appendChild(currentDialog);

        // Shift down by 100px in teacher view.
        if (reportType == 1) {
            currentDialog.style.top = '1000px';
        }

        currentDialog.querySelector('.add-comment-btn').addEventListener('click', () => {
            saveComment(cmid);
        });

        loadComments(cmid);
    }

    /**
     * Loads the comments from a specific block
     * @param {Number} cmid context module id
     * @returns {Promise<void>}
     */
    async function loadComments(cmid) {
        if (!currentDialog || !currentBlockId){
            return;
        }

        const container = document.getElementById('comments-container');

        const request = {
            methodname: 'mod_nextblocks_get_comments',
            args: { blockid: currentBlockId, cmid: cmid}
        };

        const response = await new Promise((resolve, reject) => {
            ajax.call([request])[0]
                .then(resolve)
                .catch(reject);
        });
        container.innerHTML = '';

        response.sort((a, b) => a.timecreated - b.timecreated);

        response.forEach(comment => {
            const commentDiv = document.createElement('div');
            commentDiv.className = 'comment-item';
            commentDiv.style.borderBottom = '1px solid #eee';
            commentDiv.style.padding = '8px 0';

            commentDiv.innerHTML = `
            <div class="comment-meta">
                <strong>${comment.firstname} ${comment.lastname}</strong>
                <span>${new Date(comment.timecreated * 1000).toLocaleString()}</span>
            </div>
            <div class="comment-content">${comment.content}</div>
        `;
            container.appendChild(commentDiv);
        });

        if (response.length === 0) {
            str.get_string('nocomments', 'mod_nextblocks').then(function(text) {
                container.innerHTML = '<div class="no-comments">'+text+'</div>';
            });
        }
    }

    /**
     * Saves the previously created comment
     * @param {Number} cmid context module id
     * @returns {Promise<void>}
     */
    async function saveComment(cmid) {
        if (!currentDialog || !currentBlockId){
            return;
        }

        const textarea = currentDialog.querySelector('.new-comment-text');
        const content = textarea.value.trim();
        const button = currentDialog.querySelector('.add-comment-btn');

        if (!content){
            return;
        }

        button.disabled = true;

        const request = {
            methodname: 'mod_nextblocks_save_comment',
            args: {
                blockid: currentBlockId,
                content: content,
                cmid: cmid,
            }
        };

        await new Promise((resolve, reject) => {
            ajax.call([request])[0]
                .then(resolve)
                .catch(reject);
        });

        const workspace = Blockly.getMainWorkspace();
        markBlockWithComment(currentBlockId, workspace);

        textarea.value = '';
        await loadComments(cmid);

        button.disabled = false;
    }

    /**
     * Initializes the indicators for blocks that have comments
     * @param {object} workspace blockly's workspace
     */
    function initCommentIndicators(workspace) {
        workspace.getAllBlocks().forEach(block => {
            checkBlockForComments(block.id, workspace);
        });

        workspace.addChangeListener(event => {
            if (event.type === Blockly.Events.BLOCK_CREATE) {
                setTimeout(() => {
                    checkBlockForComments(event.blockId, workspace);
                }, 500);
            }
        });
    }

    /**
     * Marks a specific block with the comment indicator
     * @param {Number} blockId the block's id
     * @param {object} workspace blockly's workspace
     */
    function markBlockWithComment(blockId, workspace) {
        const block = workspace.getBlockById(blockId);
        if (block){
            block.setWarningText(' ', 'comment_warning');
        }
    }

    /**
     * Removes the comment indicator from a specific block
     * @param {Number} blockId the block's id
     * @param {object} workspace blockly's workspace
     */
    function removeCommentIndicator(blockId, workspace) {
        const block = workspace.getBlockById(blockId);
        if (block){
            block.setWarningText(null, 'comment_warning');
        }
    }

    /**
     * Checks whether a specific block has any comments
     * @param {Number} blockId the block's id
     * @param {object} workspace blockly's workspace
     * @returns {Promise<void>}
     */
    async function checkBlockForComments(blockId, workspace) {
        const request = {
            methodname: 'mod_nextblocks_get_comments',
            args: {
                blockid: blockId,
                cmid: getCMID(),
            }
        };

        const response = await new Promise((resolve, reject) => {
            ajax.call([request])[0]
                .then(resolve)
                .catch(reject);
        });

        if (response.length > 0) {
            markBlockWithComment(blockId, workspace);
        }
        else{
            removeCommentIndicator(blockId, workspace);
        }
    }

    Blockly.registry.register(Blockly.registry.Type.TOOLBOX_ITEM, 'toolboxlabel', ToolboxLabel);

    Blockly.registry.register(Blockly.registry.Type.TOOLBOX_ITEM, Blockly.ToolboxCategory.registrationName, CustomCategory, true);

    return {
        /**
         * @param {string} contents The contents of the tests file.
         * @param {string} loadedSave The contents of the loaded save, in a base64-encoded JSON string.
         * @param {{}} customBlocks The custom blocks to be added to the toolbox, created by the exercise creator.
         * @param {number} remainingSubmissions The number of remaining submissions for the current user.
         * @param {string[]} reactions An array of 3 strings, each containing the number of reactions of a certain type
         * (easy, medium, hard).
         * @param {number} lastUserReaction The type of reaction the current user last submitted
         * (0 = no reaction, 1 = easy, 2 = medium, 3 = hard).
         * @param {number} reportType Indicates the type of report to be displayed (0 = no report, 1 = teacher report,
         * 2 = student report).
         * @param {string} userName The name of the user that loaded the page.
         * @param {number} activityId The id of the activity
         * @param {{}} blockLimits the use limit of each block
         */
        init: function(contents, loadedSave, customBlocks, remainingSubmissions, reactions, lastUserReaction, reportType = 0,
                       userName, activityId, blockLimits) {
            // If report is student but he can still submit, change to no report so he can use the workspace
            if (reportType === 2 && remainingSubmissions > 0) {
                reportType = 0;
            }

            globalReportType = reportType;

            updatePercentages(reactions[0], reactions[1], reactions[2]);

            //chat.populate(repository.getMessages, activityId);

            const blocklyDiv = document.getElementById('blocklyDiv');
            const blocklyArea = document.getElementById('blocklyArea');

            // If there are custom blocks, add a new category to the toolbox
            if (customBlocks.length > 0) {
                toolbox.contents.push({
                    'kind': 'category',
                    'name': 'Custom Blocks',
                    'colour': 'a55b80',
                    "cssConfig": {
                        'icon': 'customIcon fa-solid fa-code',
                    },
                    'contents': [],
                });
            }

            customBlocks.forEach((block) => {
                let splitTest = block.generator.split("forBlock['");
                let dotCase = false;
                if (splitTest.length < 2) {
                    splitTest = block.generator.split("forBlock.");
                    if (splitTest.length < 2) {
                        throw new Error("Invalid generator");
                    }
                    dotCase = true;
                }
                const blockName = splitTest[1].split(dotCase ? " = " : "']")[0].trim();
                // Add block to toolbox
                toolbox.contents[toolbox.contents.length - 1].contents.push({
                    'kind': 'block',
                    'type': blockName,
                });

                const definition = JSON.parse(block.definition);
                Blockly.defineBlocksWithJsonArray([definition]);
                // eslint-disable-next-line no-eval
                eval(block.generator);
                if (block.pythongenerator.length === 0) {
                    var code = "Blockly.Python.forBlock['" + blockName + "'] = function(block) {\n" +
                        "  const code = '" + blockName + "()';\n" +
                        "  return [code, Blockly.Python.ORDER_ATOMIC];\n" +
                        "};\n";
                    // eslint-disable-next-line no-eval
                    eval(code);
                } else {
                    // eslint-disable-next-line no-eval
                    eval(block.pythongenerator);
                }
            });

            Object.keys(blockLimits).forEach(function(k) {
                blockLimits[k] = parseInt(blockLimits[k], 10);
            });

            nextblocksWorkspace = Blockly.inject(blocklyDiv, getOptions(remainingSubmissions, reportType !== 0, blockLimits));
            Blockly.JavaScript.init(nextblocksWorkspace);
            Blockly.Python.init(nextblocksWorkspace);
            // Use resize observer instead of window resize event. This captures both window resize and element resize
            const resizeObserver = new ResizeObserver(() => onResize(blocklyArea, blocklyDiv, nextblocksWorkspace));
            resizeObserver.observe(blocklyArea);

            nextblocksWorkspace.getParentSvg().addEventListener('click', function(e) {
                let target = e.target;
                if (target.nodeType !== Node.ELEMENT_NODE) {
                    target = target.parentElement;
                }
                const warningIcon = target.closest('.blocklyWarningIcon, .blocklyIconGroup');
                if (!warningIcon){
                    return;
                }

                const blockSvg = warningIcon.closest('g[data-id]');
                if (!blockSvg){
                    return;
                }

                const blockId = blockSvg.getAttribute('data-id');
                if (!blockId){
                    return;
                }

                const block = nextblocksWorkspace.getBlockById(blockId);
                if (block) {
                    openCommentDialog(block, getCMID(), reportType);
                }
            });

            // Parse json from test file contents
            const tests = JSON.parse(contents);
            let inputFunctionDeclarations = {funcDecs: ""};

            // Load the save, if there is one
            if (loadedSave !== null) {
                loadSave(loadedSave, nextblocksWorkspace);
            } else { // Otherwise, add the start block
                addBlockToWorkspace('start', nextblocksWorkspace);
            }

            // If page is a report page, lock all workspace blocks while still allowing comments
            if (reportType !== 0) {
                lockWorkspaceBlocks(nextblocksWorkspace);
            }

            initCommentIndicators(nextblocksWorkspace);

            setupButtons(tests, nextblocksWorkspace, inputFunctionDeclarations.funcDecs,
                lastUserReaction, reportType === 1, blockLimits);

            //chat.run(userName, activityId, repository.saveMessage);
        },
    };

    /**
     * Locks all blocks in a workspace, preventing them from being moved or deleted
     * @param {WorkspaceSvg} workspace The workspace to lock
     */
    function lockWorkspaceBlocks(workspace) {
        workspace.getTopBlocks(false).forEach((block) => {
            lockBlock(block);
            lockChildren(block);
        });

        /**
         * Recursively locks a block and all its children, preventing them from being moved or deleted
         * @param {BlockSvg} block The block that will be locked and have its children locked
         */
        function lockChildren(block) {
            block.getChildren(false).forEach((child) => {
                lockBlock(child);

                // Have to mess with internal Blockly stuff to block only the inputs while still allowing comments
                child.inputList.forEach((input) => {
                    input.fieldRow.forEach((field) => {
                        field.setEnabled(false);
                    });
                });

                lockChildren(child);
            });
        }

        /**
         * Locks a block, preventing it from being moved or deleted
         * @param {BlockSvg} block The block that will be locked
         */
        function lockBlock(block) {
            block.setMovable(false);
            block.setDeletable(false);
        }
    }

    /**
     * Updates the percentages of difficulty levels (easy, medium, hard) on the page.
     *
     * @param {number} easy - The count of 'easy' reactions.
     * @param {number} medium - The count of 'medium' reactions.
     * @param {number} hard - The count of 'hard' reactions.
     * @param {string} [inc=""] - The difficulty level to increment. If not provided, no level is incremented.
     * Unused right now, just for future-proofing
     */
    function updatePercentages(easy, medium, hard, inc = "") {
        // Mapping of difficulty levels to their corresponding HTML elements
        const elements = {
            "easy": document.getElementById('percentage-easy'),
            "medium": document.getElementById('percentage-medium'),
            "hard": document.getElementById('percentage-hard')
        };

        // Mapping of difficulty levels to their counts
        const values = {
            "easy": easy,
            "medium": medium,
            "hard": hard
        };

        // If a difficulty level to increment is provided, increment its count
        if (inc in values) {
            values[inc]++;
        }

        // Calculate the percentages for each difficulty level
        let percentages = calcPercentages(values.easy, values.medium, values.hard);

        // Update the HTML elements with the new percentages
        elements.easy.innerHTML = percentages[0] + '%';
        elements.medium.innerHTML = percentages[1] + '%';
        elements.hard.innerHTML = percentages[2] + '%';
    }

    /**
     *
     * @param {number} easy - The count of 'easy' reactions.
     * @param {number} medium - The count of 'medium' reactions.
     * @param {number} hard - The count of 'hard' reactions.
     * @returns {number[]|number[]} - The percentages of each reaction.
     */
    function calcPercentages (easy, medium, hard) {
        const total = easy + medium + hard;
        return total === 0 ? [0, 0, 0] : [easy, medium, hard].map(val => Math.round((val / total) * 100));
    }

    /**
     *
     * @param {number} remainingSubmissions how many times can the user submit
     * @param {bool} readOnly whether the user can interact or not with the toolbox
     * @param {number[]} blockLimits the limits of each block
     * @returns {toolboxPosition} the options of the toolbox
     */
    function getOptions (remainingSubmissions, readOnly, blockLimits) {
        return {
            toolbox: readOnly ? null : toolbox,
            collapse: true,
            comments: false,
            disable: false,
            maxBlocks: Infinity,
            trashcan: !readOnly,
            horizontalLayout: false,
            toolboxPosition: 'start',
            css: true,
            media: 'https://blockly-demo.appspot.com/static/media/',
            rtl: false,
            scrollbars: true,
            sounds: true,
            oneBasedIndex: false,
            readOnly: remainingSubmissions <= 0,
            grid: {
                spacing: 20,
                length: 1,
                colour: '#888',
                snap: false,
            },
            zoom: {
                controls: true,
                wheel: true,
                startScale: 1,
                maxScale: 3,
                minScale: 0.3,
                scaleSpeed: 1.2,
            },
            maxInstances: blockLimits,
        };
    }

    /**
     * Handles window resize
     * @param {object} blocklyArea
     * @param {object} blocklyDiv
     * @param {object} nextblocksWorkspace
     */
    function onResize(blocklyArea, blocklyDiv, nextblocksWorkspace) {
        // Compute the absolute coordinates and dimensions of blocklyArea.
        let element = blocklyArea;
        let x = 0;
        let y = 0;
        do {
            x += element.offsetLeft;
            y += element.offsetTop;
            element = element.offsetParent;
        } while (element);
        // Position blocklyDiv over blocklyArea.
        blocklyDiv.style.left = x + 'px';
        blocklyDiv.style.top = y + 'px';
        blocklyDiv.style.width = blocklyArea.offsetWidth + 'px';
        blocklyDiv.style.height = blocklyArea.offsetHeight + 'px';
        Blockly.svgResize(nextblocksWorkspace);
    }

    /**
     * @param {String} blockName The name of the input block to be added (prompt on the left side of the block
     * @param {WorkspaceSvg} workspace The workspace to add the input block to
     * @returns {BlockSvg} The newly created block
     */
    function addBlockToWorkspace(blockName, workspace) {
        const newBlock = workspace.newBlock(blockName);
        newBlock.initSvg();
        newBlock.render();
        return newBlock;
    }

    /**
     * @param {String} loadedSave
     * @param {WorkspaceSvg} workspace
     */
    function loadSave(loadedSave, workspace) {
        const state = JSON.parse(atob(loadedSave));
        Blockly.serialization.workspaces.load(state, workspace);
    }

    /**
     * @returns {Number} The course module id of the current page
     */
    function getCMID() {
        const classList = document.body.classList;
        const cmidClass = Array.from(classList).find((className) => className.startsWith('cmid-'));
        return parseInt(cmidClass.split('-')[1]);
    }

    /**
     * @param {string} prompt The name of the input block to be added (prompt on the left side of the block)
     * @param {string} inputType The type of the input block to be added (string, number, etc.)
     * @param {object} inputFunctionDeclarations Contains the string containing the function declarations for the input
     * blocks, to be added to the top of the code. Is an object so that it is passed by reference.
     */

    // eslint-disable-next-line no-extend-native
    String.prototype.hideWrapperFunction = function() {
        const lines = this.split('\n');
        lines.splice(0, 2); // Remove the first two lines
        return lines.join('\n');
    };

});