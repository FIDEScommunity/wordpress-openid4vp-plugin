<?php
/**
 * Receive Heartbeat data and respond.
 *
 * Processes data received via a Heartbeat request, and returns additional data to pass back to the front end.
 *
 * @param array $response Heartbeat response data to pass back to front end.
 * @param array $data     Data received from the front end (unslashed).
 *
 * @return array
 */
function myplugin_receive_heartbeat( array $response, array $data ) {
	// If we didn't receive our data, don't send any back.
	if ( empty( $data['myplugin_customfield'] ) ) {
		return $response;
	}

	// Calculate our data and pass it back. For this example, we'll hash it.
	$received_data = $data['myplugin_customfield'];

	$response['myplugin_customfield_hashed'] = sha1( $received_data );
	return $response;
}
add_filter( 'heartbeat_received', 'myplugin_receive_heartbeat', 10, 2 );


