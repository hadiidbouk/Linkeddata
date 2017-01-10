
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

if(!isset($_GET['sbj']) || !isset($_GET['nb']) || !isset($_GET['offnb']) | !isset($_GET['first'])){
	
    // Return error message
    die( header('HTTP/1.0 500 Internal Server Error'));
}


$subj = $_GET['sbj']=="Gun"?"Gun":"Missile";
$limit =$_GET['nb'];
$first = $_GET['first'];
$offnb = $_GET['offnb'];


	$q = $query.'SELECT  ?Title ?Description ?Picture WHERE {
					?gun rdfs:label ?Title;
					     dbo:abstract ?Description;
					     dbo:thumbnail ?Picture.
					filter(?Title="'.$subj.'"@en)
					filter langMatches(lang(?Description) , "En")
					}';
if($first == 'true')	{	
	//with sbj		
		$sbjResult = sparql_query( $q ); 
		if( !$sbjResult ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit;}
  }

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
 					
 					 ';
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

					';
	}
// if(isset($_GET["offnb"])){

	$query = $q . "offset ".$offnb." LIMIT ".$limit;

	$itemsResult = sparql_query( $query ); 
	if( !$itemsResult ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit;}

	
	$sbjArray =  array();
	$itemsArray = array();

	if($first == 'true'){
		while( $row = sparql_fetch_array( $sbjResult ) )
		array_push($sbjArray, $row);
		$nbQuery = $q. " offset " .( $limit)." LIMIT ".$limit;
	}
	else
		$nbQuery = $q. " offset " .($offnb + $limit)." LIMIT ".$limit;
	while( $row = sparql_fetch_array( $itemsResult ) )
		array_push($itemsArray, $row);

	$nbArray  = array('Type' =>$subj );
	$nbResult = sparql_query( $nbQuery ); 
	if( !$nbResult ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit;}
	// $x=(string)sparql_num_rows($nbResult);
	// echo "rowsnb : $x/// subject : $subj/// limit : $limit/// offnb : $offnb /// first : $first///q:$nbQuery";
	// exit();
	if(sparql_num_rows($nbResult) < $limit)
		$nbArray  = array('off' =>'false' );

	else
		$nbArray  = array('off' =>'true' );



	$merge = array_merge($nbArray,$sbjArray); 
	$merge = array_merge($merge,$itemsArray); 

	echo json_encode($merge);
// }else{
// 	$nbQuery = $q. " offset " .($_GET["nb"])." LIMIT ".$limit;

// 	$rows3 = sparql_query( $nbQuery ); 

// 	if(sparql_num_rows($rows3) < $limit)
// 		$nbArray  = array('off' =>'false' );

// 	else
// 		$nbArray  = array('off' =>'true' );


// // 	$q.=" LIMIT ".$limit;
// $rows2 = sparql_query( $q ); 
// if( !$rows2 ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }
// $sbjArray =  array();
// $itemsArray = array();



// //sure it's a wrong way to do it but it's sparqllib..
// while( $row = sparql_fetch_array( $rows ) )
// 	array_push($sbjArray, $row);
// while ($row = sparql_fetch_array($rows2))
// 	array_push($itemsArray, $row);

// $merge = array_merge($nbArray,$sbjArray); 

// $merge = array_merge($merge, $itemsArray); 
// echo json_encode($merge);
// //}






?>
