The-Public-Spheres
==================

A web application designed for many to many debates

Demo: http://thepublicspheres.com/

Setup:

1. Create a file named colorConverter.js in the root directory and copy the JavaScript function hsvToRgb(h, s, v) from http://mjijackson.com/2008/02/rgb-to-hsl-and-rgb-to-hsv-color-model-conversion-algorithms-in-javascript into that file.

2. Install ruby on your local machine. Type "gem update --system" into a command line terminal. Type "gem install compass" into a command line terminal to install Compass. Type "compass compile styles" to compile the .scss files into css files (do this anytime you make a change to a .scss file. Create a folder styles on your server. Upload the stylesheets folder that is inside the styles folder on your computer to your server.

3. Create a MySQL database and import the file kaplanex_publicSpheres.sql which contains the table structure information.

4. Create a folder in the root directory titled db and create a file in it titled config.php. Set the folder and file's permissions only allow owner and group read access and disable all other permissions. In config.php, create a php block that sets the following variables: $host, $username , $password, $db to the values corresponding to your MySQL database.

5. Change the permissions of user-man.php, pwqcheck.php, and PasswordHash.php to only allow owner and group read, write, execute access.

Notes:
The user accounts system is powered by phpass and sample code found here http://www.openwall.com/articles/PHP-Users-Passwords. phpass and the sample code were released both into the public domain and under a heavily cut-down "BSD license", to the point of being copyright only.