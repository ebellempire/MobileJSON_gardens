<?php

// Get the basic data
$itemMetadata = array(
   'id'           => $item->id,
   'subject'      => metadata( 'item', array( 'Dublin Core', 'Subject' ) ),
   'description'  => metadata( 'item', array( 'Dublin Core', 'Description' ) ),
   'creator'      => metadata( 'item', array( 'Dublin Core', 'Creator' ) ),
   'source'       => metadata( 'item', array( 'Dublin Core', 'Source' ) ),
   'publisher'    => metadata( 'item', array( 'Dublin Core', 'Publisher' ) ),
   'date'         => metadata( 'item', array( 'Dublin Core', 'Date' ) ),
   'visibility'	  => metadata( 'item', array( 'Item Type Metadata', 'Access status' ))
);

$itemMetadata['title'] = html_entity_decode(
   strip_formatting( metadata( 'item', array( 'Dublin Core', 'Title' ) ) ) );

//
// FILES
//
$files = array();
foreach( $item->Files as $file )
{
   $path = $file->getWebPath( 'original' );

   $mimetype = metadata( $file, 'MIME Type' );
   $filedata = array(
      'id'        => $file->id,
      'mime-type' => $mimetype,
      'size'      => $file->size );

   $title = metadata( $file, array( 'Dublin Core', 'Title' ) );
   if( $title ) {
      $filedata['title'] = strip_formatting( $title );
   }

   if( strpos( $mimetype, 'image/' ) === 0 ) {
      list( $width, $height ) = getimagesize( $file->getWebPath( 'original' ) );
      $filedata[ 'width' ] = $width;
      $filedata[ 'height' ] = $height;
   }

   $description = metadata( $file, array( 'Dublin Core', 'Description' ) );
   if( $description ) {
      $filedata['description'] = $description;
   }

   if( $file->hasThumbnail() ) {
      $filedata['thumbnail'] = $file->getWebPath( 'square_thumbnail' );
   }

   $files[ $path ] = $filedata;
}

if( count( $files ) > 0 )
{
   $itemMetadata['files'] = $files;
}

//
// LOCATION
//
// Get the location of the object (if Geolocation is an enabled plugin)
//
$location = get_db()->getTable(
   'Location' )->findLocationByItem( $item, true );
if( $location ) {
   $itemLatitude = $location['latitude'];
   $itemLongitude = $location['longitude'];

   $itemMetadata = array_merge( $itemMetadata,
      array(
         'latitude' => $itemLatitude,
         'longitude' => $itemLongitude,
      )
   );

   /* DISABLED: I don't know where this function comes from.
   if( $itemLatitude && $itemLongitude ) {
      $itemMetadata['distance_away_miles'] = geocode_measure_distance(
         $_GET['latitude'], $_GET['longitude'],
         $itemLatitude, $itemLongitude );
   }
    */
}

// I've heard that the Zend JSON encoder is really slow,
// if this becomes a problem, use the second line.
echo Zend_Json_Encoder::encode( $itemMetadata );
//echo json_encode( $itemMetadata );