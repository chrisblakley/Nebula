# Copilot Instructions

## Code Formatting Guidelines

1. **Conditionals**:
	- Always add spaces inside the parentheses of conditionals.
	  ```
	  //Correct
	  if ( condition ){
			// code
	  }

	  //Incorrect
	  if(condition){
			// code
	  }
	  ```

2. **Indentation**:
	- Always use tabs instead of spaces for indentation.
	  ```
	  //Correct
	  function example() {
	  ↹let x = 1;
	  }

	  //Incorrect
	  function example() {
			let x = 1;
	  }
	  ```

3. **Comments**:
	- Never put a space between the `//` and the first character of the comment.
	  ```
	  //Correct
	  //This is a comment

	  // Incorrect
	  // This is a comment
	  ```

4. **CSS and SASS**:
	- Write CSS and SASS rules in a single line, but nesting is allowed.
	  ```
	  //Correct
	  .container {display: flex; gap: 5px;
	  	.item {margin: 10px;}
	  }

	  //Incorrect
	  .container {
	  	display: flex;
	  	.item {
	  		margin: 10px;
		}
	  }
	  ```

Please follow these guidelines to ensure consistency and readability in the codebase.