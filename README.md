#Nebula
Nebula is a springboard WordPress theme framework for developers. Like other WordPress startup themes, it has custom functionality built-in (like shortcodes, styles, and JS/PHP functions), but unlike other themes it is not meant for the end-user.

![Nebula](https://gearside.com/nebula/wp-content/themes/Nebula-master/screenshot.png)

Wordpress developers will find all source code not obfuscated, so everything may be customized and altered to fit the needs of the project. Additional comments have been added to help explain what is happening; not only is this framework great for speedy development, but it is also useful for learning advanced Wordpress techniques.

##Installation
- [Download the .zip file of the Nebula theme.](https://github.com/chrisblakley/Nebula/archive/master.zip) Extract and upload to the /themes directory via FTP.
- Activate the Nebula theme and run the automated initialization.
- *(Optional)* To use the prepared child theme, move the Nebula-Child directory to the /themes/ directory.
- *(Optional)* Activate the Nebula Child theme.

##Using Child Themes in WordPress
Child themes allow customization without crippling the original theme. This means that the parent Nebula can be easily updated without fear of breaking custom code. All customizations should be done in the child theme by copying the parent files as needed to the appropriate subdirectory within the child theme. When doing this, the file(s) in the child theme will override the file in the parent (except for functions.php and style.css). The main functions.php file in the child theme is run **before** the functions.php file in the parent. The style.css in the child theme is run **after** the style.css in the parent.

**Important Notes:**
- If renaming either theme, be sure to update the directories to match. The themes can be named however, but it is strongly recommended that the child theme directory match the parent with "-child" added to the end.
- Make sure the "Template:" setting at the top of the child theme's style.css (or stylesheets/scss/style.scss if using SASS) matches the template directory of the parent theme!

##Additional Guides
- *[Setup Guide](https://gearside.com/nebula/documentation/get-started/)*
- *[Testing Checklist](https://gearside.com/nebula/documentation/get-started/testing-checklist/)*
- *[Launch Guide](https://gearside.com/nebula/documentation/get-started/launch-checklist/)*

##Documentation
Full documentation is available at [https://gearside.com/nebula](https://gearside.com/nebula) including custom functionality as well as examples of useful snippets.