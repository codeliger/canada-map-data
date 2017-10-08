<?php

/*
    I got the smaller versions of the canada gml files
    I need to run the php with the new data / new names
    I need to try and import it into dev
    I need to import the states and cities for the united states
    I need to get the import statements working for the us
*/

ini_set('memory_limit','-1');
error_reporting(E_ERROR | E_WARNING | E_PARSE);

require('vendor/autoload.php');
require('functions/GMLtoSQL.php');
require('functions/KMLtoSQL.php');

// since we arent using a database we must define our own escape function
function escape($value)
{
    $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
    $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

    return str_replace($search, $replace, $value);
}

define('MAPLEVEL_COUNTRY',1);
define('MAPLEVEL_STATEORPROVINCE',2);
define('MAPLEVEL_CITYORMUNICIPALITY',3);
define('MAPLEVEL_DISTRICTORNEIGHBOURHOOD',4);
define('MAPLEVEL_POSTALCODE',4);
define('MAPLEVEL_ELECTORALDISTRICT',5);

// GMLtoSQL('gml/canada/province.gml','sql/Canada_Region.sql',MAPLEVEL_STATEORPROVINCE,'PRENAME'); # Provinces & Territories
// GMLtoSQL('gml/canada/populationcentre.gml','sql/Canada_PopulationCentre.sql',MAPLEVEL_CITYORMUNICIPALITY,'PCNAME'); # Population Centres (1000 or more people)
// GMLtoSQL('gml/canada/electoraldistrict.gml','sql/Canada_FederalElectionDistrict.sql',MAPLEVEL_ELECTORALDISTRICT,'FEDNAME'); # Federal Election Districts
// GMLtoSQL('gml/canada/postalcode.gml','sql/Canada_PostalCode.sql',MAPLEVEL_POSTALCODE,'CFSAUID'); # First 3 digits of postal code
GMLtoSQL('gml/canada/country.gml','sql/Canada.sql',MAPLEVEL_COUNTRY,'NAME');

// KMLtoSQL('gml/usa/cb_2016_us_nation_20m.kml','sql/usa_nation.sql',MAPLEVEL_COUNTRY,'SimpleData','NAME'); // Country
// KMLtoSQL('gml/usa/cb_2016_us_state_20m.kml','sql/usa_state.sql',MAPLEVEL_STATEORPROVINCE,'SimpleData','NAME'); // States
// KMLtoSQL('gml/usa/cb_2016_us_county_20m.kml','sql/usa_county.sql',MAPLEVEL_DISTRICTORNEIGHBOURHOOD,'SimpleData','NAME'); // Countys
// KMLtoSQL('gml/usa/cb_2016_us_ua10_500k.kml','sql/usa_ua.sql',MAPLEVEL_CITYORMUNICIPALITY,'SimpleData','NAME10'); // Urban Areas
// KMLtoSQL('gml/usa/cb_2016_us_zcta510_500k.kml','sql/usa_zip.sql',MAPLEVEL_POSTALCODE,'SimpleData','GEOID10'); // ZIP codes


//KMLtoSQL('gml/usa/cb_2016_us_cd115_20m.kml','sql/usa_census.sql',MAPLEVEL_ELECTORALDISTRICT, 'SimpleData','CD115FP');