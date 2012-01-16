<?php
class ezpDfsMySQLiClusterGateway extends ezpClusterGateway
{
    public function getDefaultPort()
    {
        return 3306;
    }

    public function connect( $host, $port, $user, $password, $database, $charset = 'utf8' )
    {
        if ( !$this->db = mysqli_connect( $host, $user, $password, $database, $port ) )
            throw new RuntimeException( "Failed connecting to the MySQL database" );

        if ( !mysqli_set_charset( $this->db, $charset ) )
            throw new RuntimeException( "Failed to set database charset to '$charset'");
    }

    public function fetchFileMetadata( $filepath )
    {
        $filePathHash = md5( $filepath );
        $sql = "SELECT * FROM ezdfsfile WHERE name_hash='$filePathHash'" ;
        if ( !$res = mysqli_query( $this->db, $sql ) )
            throw new RuntimeException( "Failed to fetch file metadata for '$filepath'" );

        if ( mysqli_num_rows( $res ) == 0 )
        {
            return false;
        }

        $metadata = mysqli_fetch_assoc( $res );
        mysqli_free_result( $res );
        return $metadata;
    }

    public function passthrough( $filepath, $offset = false, $length = false)
    {
        $dfsFilePath = CLUSTER_MOUNT_POINT_PATH . '/' . $filepath;

        if ( !file_exists( $dfsFilePath ) )
            throw new RuntimeException( "Unable to open DFS file '$dfsFilePath'" );

        $fp = fopen( $dfsFilePath, 'r' );
        fpassthru( $fp );
        fclose( $fp );
    }

    public function close()
    {
        mysqli_close( $this->db );
        unset( $this->db );
    }
}

// return the class name for easier instanciation
return 'ezpDfsMySQLiClusterGateway';