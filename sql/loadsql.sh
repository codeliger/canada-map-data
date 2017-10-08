#!/bin/sh
echo Loading Canada
mysql -u polluser -p -h thinkster.ca polldev < Canada_Region.sql
echo Loading Postal Codes
mysql -u polluser -p -h thinkster.ca polldev < Canada_PostalCode.sql
echo Loading Cities
mysql -u polluser -p -h thinkster.ca polldev < Canada_PopulationCentre.sql
echo Loading Election Districts
mysql -u polluser -p -h thinkster.ca polldev < Canada_FederalElectionDistrict.sql
echo Loading USA
mysql -u polluser -p -h thinkster.ca polldev < usa_nation.sql
echo Loading Countys
mysql -u polluser -p -h thinkster.ca polldev < usa_county.sql
echo Loading States
mysql -u polluser -p -h thinkster.ca polldev < usa_state.sql
echo Loading Urban areas
mysql -u polluser -p -h thinkster.ca polldev < usa_ua.sql
echo Loading Zip Codes
mysql -u polluser -p -h thinkster.ca polldev < usa_zip.sql # geo problem

