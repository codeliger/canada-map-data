<?php
    set_time_limit(0);
    
    /*
        Imports a KMl file into MySQL
    */
    function KMLtoSQL($gmlFilePath, $outputName, $mapLevel, $nameElement, $nameAttribute=null){

        try{
            printf("\nConverting file %s to sql.",$gmlFilePath);
            if(file_exists($outputName)){
                unlink($outputName);
            }
            $file = fopen($outputName,'x');

            $rawContents = file_get_contents($gmlFilePath);
            $xmlContents = DomDocument::loadXML($rawContents);
            $Placemarks = $xmlContents->getElementsByTagName('Placemark');

            foreach($Placemarks as $indexa=>$Placemark){
                if(!is_null($nameAttribute)){
                    $elements =  $Placemark->getElementsByTagName($nameElement);
                    foreach($elements as $e){
                        
                        if($e->getAttribute("name") == "$nameAttribute"){
                            $name = addslashes($e->nodeValue);
                        }
                    }               
                }
                else{
                    $name = escape($Placemark->getElementsByTagName($nameElement)->item(0)->nodeValue);                                      
                }

                fwrite($file,"insert into MapRegion (MapLevelId,Name,Region,IsActive) values ");

                // gml coordinate pairs are seperated by spaces instead of commas.
                $Polygons = $Placemark->getElementsByTagName('Polygon');
                $polycount = $Polygons->length;
                // if($indexa > 0){
                //     fwrite($file,',');
                // }
                fwrite($file,"/* $polycount Polygons */ ($mapLevel,'$name',ST_PolyFromText('POLYGON(");

                foreach($Polygons as $index=>$Polygon){
                    if($index > 0){
                        fwrite($file,',');
                    }
                    $matchedPositions = [];
                    preg_match_all('/(-?\d+\.\d+),(-?\d+\.\d+),-?\d+\.\d+/',$Polygon->nodeValue,$matchedPositions,PREG_PATTERN_ORDER);
                    /*
                        To import into mysql the first and last co-ordinate pair must match
                        We must check if they match and if they dont manually add the last point
                    */
                    if($matchedPositions[1][0] != $matchedPositions[1][count($matchedPositions[1])-1] || $matchedPositions[2][0] != $matchedPositions[2][count($matchedPositions[1])-1]){
                        
                        // print("\n The beginning and end of insert $indexa polygon $index do not match.");
                        // print("\n" . $indexa . ": bx " . $matchedPositions[1][0]);
                        // print("\n" . $indexa . ": ex " . $matchedPositions[1][count($matchedPositions[1])-1]);
                        // print("\n" . $indexa . ": by " .$matchedPositions[2][0]);
                        // print("\n" . $indexa . ": ey " . $matchedPositions[2][count($matchedPositions[1])-1]);

                        $matchedPositions[1][] = $matchedPositions[1][0];
                        $matchedPositions[2][] = $matchedPositions[2][0];

                        // print("\n New values:");
                        // print("\n" . $indexa . ": bx " . $matchedPositions[1][0]);
                        // print("\n" . $indexa . ": ex " . $matchedPositions[1][count($matchedPositions[1])-1]);
                        // print("\n" . $indexa . ": by " .$matchedPositions[2][0]);
                        // print("\n" . $indexa . ": ey " . $matchedPositions[2][count($matchedPositions[1])-1]);
                    }

                    fwrite($file,'(');
                    for($i = 0; $i < count($matchedPositions[1]); $i++){
                        if($i > 0){
                            fwrite($file,',');
                        }
                        fwrite($file,$matchedPositions[1][$i] . " " . $matchedPositions[2][$i]);
                    }
                    fwrite($file,')');

                }
                // MySQL requires the code relating to how the data is encoded in the database
                fwrite($file,")',4326),1);\n");
            }

        }catch(Exception $e){

            print($e->getMessage());
            print_r($file);

        }finally{
            fclose($file);
        }
    }

