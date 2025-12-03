# NextBlocks User Manual #

A guide to NextBlocks installation is available in the [README page](README.md).

## Exercise Creation ##

1. NextBlocks is a Block Programming environment integrated as a Moodle activity plugin that allows teachers to create interactive and customizable block‑based programming exercises for their students. To set up a new exercise, teachers first turn on editing in their course, add the NextBlocks activity and fill in the basic details such as the activity name and description.
2. Under the Tests section, teachers can upload optional text files including automatic test cases that can be used to automatically evaluate students' programs. For each test case, teachers need to upload 2 files named inputX and outputX, where X is the number of the test case (in no specific order). Each input file has the input values for that test, one per line. Each output file has the expected output for that test case. Examples of input and output test files are present in this project under the name input1.txt and output1.txt.
3. Next, in the Custom Blocks section, teachers can define their own blocks by pasting a JSON definition and providing generator code for both JavaScript (mandatory to run the program) and Python (optional, used only for block to text code translation). A hyperlink to the blockly block generator is present in this tab where teachers can create the definition and generators interactively.
4. In the Block Limits section, they can specify how many times each block type may be used.
5. Finally, they configure grading options such as the maximum grade and resubmission settings and then save the activity, making it available to students.

## Student Interface ##

1. Students open the NextBlocks activity by clicking its link in the course. They are presented with a Blockly workspace containing the permitted blocks in the toolbox on the right. 
2. After dragging blocks into the workspace and snapping them together, students can click Run to execute their program interactively. If the program uses input blocks, the execution will pause and a prompt will appear in the terminal to collect the input. 
3. On the top left of the interface students can open the block to text code translation, where they will be shown a read-only view with the automatic translation of their program to JavaScript and Python. 
4. To provide feedback and communication between students and teachers there is a chat functionality and a reaction bar. 
5. To ensure their solution meets the teacher’s requirements, students can click Run tests to compare their output against each test case (if these were made available by the teacher); the results appear in a collapsible list showing the inputs, expected output, actual output and a pass/fail badge. 
6. When satisfied, students use the Save button to store their work or the Submit button to send their final solution for grading.

## After Submission ##

After submission, teachers can review each student’s program and automated test grading through the activity’s grading interface. They may modify pre-assigned grades and right‑click on blocks to leave feedback. All grades and feedback are seamlessly integrated into Moodle’s gradebook, providing a complete end‑to‑end workflow for creating, solving and grading block‑based programming exercises.