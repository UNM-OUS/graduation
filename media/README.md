# Site media directory

Media files placed in this directory will be served through Digraph as if they were in the web root. Primarily this is to allow adding additional CSS or JS to your site in a simple and efficient manner.

## Compiled files

Core CSS/JS files can have additional code added to them by adding a file in a folder named for the file to be modified, with `.d` appended. For example, to add CSS to `digraph.css` you could create a file here named `digraph.css.d/site-styles.css`