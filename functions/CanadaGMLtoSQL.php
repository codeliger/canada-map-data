<?php
    //nad83 to wgs84
    set_time_limit(0);

    use proj4php\Proj4php;
    use proj4php\Proj;
    use proj4php\Point;
    
    /*
        Creates a sql file from a GML file.
        Converts it to the KML standard format to be imported to MySQL
        You must specify the xml tag name that holds the name of the location you are searching through
    */
    function CanadaGMLToSQL($gmlFilePath, $outputName, $mapLevel, $regionXMLName){

        try{
            printf("\nConverting file %s to sql.",$gmlFilePath);
            $proj = new Proj4php();
            // Add statistics canada format
            $proj->addDef('EPSG:3347','+proj=lcc +lat_1=49 +lat_2=77 +lat_0=63.390675 +lon_0=-91.86666666666666 +x_0=6200000 +y_0=3000000 +ellps=GRS80 +datum=NAD83 +units=m +no_defs');
            $projWGS84 = new Proj('EPSG:4326', $proj); // KML Google Format
            $projNAD83 = new Proj('EPSG:3347', $proj); // http://convertFrom.org/ref/epsg/3347/ GML Statistics Canada format

            if(file_exists($outputName)){
                unlink($outputName);
            }
            $file = fopen($outputName,'x');

            $rawContents = file_get_contents($gmlFilePath);
            $xmlContents = DomDocument::loadXML($rawContents);
            $featureMembers = $xmlContents->getElementsByTagName('featureMember');
            $inserts = [];
            foreach($featureMembers as $indexa=>$featureMember){
                $locationName =  escape($featureMember->getElementsByTagName($regionXMLName)->item(0)->nodeValue);
                // gml coordinate pairs are seperated by spaces instead of commas.
                $posLists = $featureMember->getElementsByTagName('coordinates');
                // if($indexa > 0){
                //     fwrite($file,',');
                // }
                fwrite($file,"\ninsert into MapRegion (MapLevelId,Name,Region,IsActive) values ($mapLevel,'$locationName',ST_PolyFromText('POLYGON(");
                foreach($posLists as $index=>$pos){
                    if($index > 0){
                        fwrite($file,',');
                    }
                    $matchedPositions = [];
                    preg_match_all('/(-?\d+\.\d+)\s(-?\d+\.\d+)/',$pos->nodeValue,$matchedPositions,PREG_PATTERN_ORDER);
                    $implodablePairs = [];
                    /*
                        To import into mysql the first and last co-ordinate pair must match
                        We must check if they match and if they dont manually add the last point
                    */
                    if($matchedPositions[1][0] != $matchedPositions[1][count($matchedPositions[1])-1] || $matchedPositions[2][0] != $matchedPositions[2][count($matchedPositions[1])-1]){
                        // echo "\nThe first and last co-ordinate points did not match for $locationName polygon $index";
                        // printf("\nOld End: %s %s  New End: %s %s",$matchedPositions[1][count($matchedPositions[1])-1],$matchedPositions[2][count($matchedPositions[1])-1],$matchedPositions[1][0],$matchedPositions[2][0]);
                        $matchedPositions[1][] = $matchedPositions[1][0];
                        $matchedPositions[2][] = $matchedPositions[2][0];
                    }

                    fwrite($file,'(');
                    for($i = 0; $i < count($matchedPositions[1]); $i++){

                        // Read the proj4php documentation, transforming 1 cordinate system to another
                        $point = new Point($matchedPositions[1][$i],$matchedPositions[2][$i],$projNAD83);
                        $result = $proj->transform($projWGS84,$point)->toArray();

                        if($i > 0){
                            fwrite($file,',');
                        }
                        fwrite($file,$result[0] . " " . $result[1]);
                    }
                    fwrite($file,')');
                }
                fwrite($file,")',4326),1);");
            } // end featurememeber
        }catch(Exception $e){
            print($e->getMessage());
            print_r($file);
        }finally{
            fclose($file);
        }
    }

