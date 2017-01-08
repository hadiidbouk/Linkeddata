
<?php

require_once( "sparqllib.php" );

$db = sparql_connect( "http://dbpedia.org/sparql" );
if( !$db ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }


$query = '
	PREFIX dbo: <http://dbpedia.org/ontology/>
  	PREFIX owl: <http://www.w3.org/2002/07/owl#>
  	PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
  	PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
  	PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
  	PREFIX foaf: <http://xmlns.com/foaf/0.1/>
  	PREFIX dc: <http://purl.org/dc/elements/1.1/>
  	PREFIX : <http://dbpedia.org/resource/>
  	PREFIX dbpedia2: <http://dbpedia.org/property/>
  	PREFIX dbpedia: <http://dbpedia.org/>
  	PREFIX dbpprop: <http://dbpedia.org/property/> ';

if(!isset($_GET['sbj']) || !isset($_GET['nb']) ){
	
    // Return error message
    die( header('HTTP/1.0 500 Internal Server Error'));
}


$subj = $_GET['sbj']=="Gun"?"Gun":"Missile";
$limit =$_GET['nb'];
	$q = $query.'SELECT  ?Title ?Description ?Picture WHERE {
					?gun rdfs:label ?Title;
					     dbo:abstract ?Description;
					     dbo:thumbnail ?Picture.
					filter(?Title="'.$subj.'"@en)
					filter langMatches(lang(?Description) , "En")
					}';
$rows = sparql_query( $q ); 
if( !$rows ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }
  

	if($subj == "Gun"){
		$q= $query.' select DISTINCT ?Name (Min(?n) as ?Origin) ?Description ?Length ?Weight  ?Picture WHERE {
 					 ?gun dbo:type  :Assault_rifle;
					      foaf:name ?Name;
					      dbo:abstract ?Description;
 					      dbo:length ?Length;
					      dbo:weight ?Weight;
 					      dbo:origin ?Origin;
 					      dbo:thumbnail ?Picture.
 					?Origin foaf:name ?n.
 					filter langMatches(lang(?Description) , "En")

 					}
 					
 					 LIMIT '.$limit;
	}
	else{
		$q= $query.'SELECT DISTINCT ?Name Min(?Origin) as ?Origin ?Description ?Picture  WHERE {
					 ?missile dbo:type :Anti-tank_missile;
					      dbo:origin ?OriginG;
					      dbo:abstract ?Description;
					      rdfs:label ?Name;
					      dbo:thumbnail ?Picture.
					    ?OriginG rdfs:label ?Origin.

					filter langMatches(lang(?Name) , "En")
					filter langMatches(lang(?Origin) , "En")
					filter langMatches(lang(?Description) , "En")
					}
					order by Desc(strlen(str(?Description)))

					LIMIT '.$limit;
	}

$rows2 = sparql_query( $q ); 
if( !$rows2 ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }
$sbjArray =  array();
$itemsArray = array();


//sure it's a wrong way to do it but it's sparqllib..
while( $row = sparql_fetch_array( $rows ) )
	array_push($sbjArray, $row);
while ($row = sparql_fetch_array($rows2))
	array_push($itemsArray, $row);


$merge = array_merge($sbjArray, $itemsArray); 
echo json_encode($merge);






function FormatSubjectHTML($s){

}

class subject{
	public  $Title;
	public  $Description;
	public  $ImgURL;
}
?>
