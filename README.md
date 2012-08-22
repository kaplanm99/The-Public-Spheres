The-Public-Spheres
==================

A web application designed for many to many debates

Setup:

1. Create a file named colorConverter.js in the root directory and copy the JavaScript function hsvToRgb(h, s, v) from http://mjijackson.com/2008/02/rgb-to-hsl-and-rgb-to-hsv-color-model-conversion-algorithms-in-javascript into that file.

2. Create a MySQL database with two tables using the following SQL code:

CREATE TABLE IF NOT EXISTS `Context` (
  `responseId` int(10) unsigned NOT NULL,
  `isAgree` int(10) unsigned NOT NULL,
  `parentId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`responseId`,`isAgree`,`parentId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `Responses` (
  `responseId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `responseText` varchar(1000) NOT NULL,
  PRIMARY KEY (`responseId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=46 ;

3. Create a folder in the root directory titled db and create a file in it titled config.php. Set the folder's permissions only allow owner and group read access and disable all other permissions. In config.php, create a php block that sets the following variables: $host, $username , $password, $db to the values corresponding to your MySQL database.