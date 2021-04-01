<?php
namespace App\Database;

class Firebase
{
    private $url;

    public function __construct()
    {
        $this->url = 'https://web-service-productos-default-rtdb.firebaseio.com/';
    }

    /**
     * Crea un documento nuevo de la Base de Datos
     *
     * @param string $document Nombre del documento que se va a crear
     * 
     * @param string $collection Datos de la colección nueva en formato JSON
     * 
     * @return string Respuesta de éxito o fracaso
     */
    public function create_document($document, $collection)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url.$document.'.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH" );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $collection);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        curl_close($ch);

        return json_decode($response);
    }

    /**
     * Crea una colección nueva dentro de un documento existente
     *
     * @param string $document Nombre del documento
     * 
     * @param string $collection Ruta o nombre de la colección que se va crear
     * 
     * @param string $fields Datos de la colección nueva en formato JSON
     * 
     * @return string Respuesta de éxito o fracaso
     */
    public function create_collection($document, $collection, $fields)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url.$document.'/'.$collection.'.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH" );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        curl_close($ch);

        return json_decode($response);
    }

    /**
     * Actualiza un documento
     * 
     * @param string $document Nombre del documento que se va a actualizar
     * 
     * @param string $fields Datos de la colección en formato JSON
     * 
     * @return string Respuesta de éxito o fracaso
     */
    public function update_document($document, $fields)
    {
        $ch =  curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url.$document.'.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        if( !is_null(json_decode($response)) ) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT" );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));
            $response = curl_exec($ch);
        }
    
        curl_close($ch);
    
        // Se convierte a Object o NULL
        return json_decode($response);
    }

    /**
     * Actualiza una colección completa en una ruta específica
     * 
     * @param string $document Nombre del documento al que pertenece la colección
     * 
     * @param string $collection Ruta o nombre de la colección que se va actualizar
     * 
     * @param string $fields Datos de la colección en formato JSON
     * 
     * @return string Respuesta de éxito o fracaso
     */
    public function update_collection($document, $collection, $fields)
    {
        $ch =  curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url.$document.'/'.$collection.'.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        if( !is_null(json_decode($response)) ) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT" );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));
            $response = curl_exec($ch);
        }
    
        curl_close($ch);
    
        // Se convierte a Object o NULL
        return json_decode($response);
    }

    /**
     * Actualiza uno o varios datos de una colección
     *
     * @param string $collection Ruta de la colección que se va actualizar (incluyendo el nombre del documento)
     * 
     * @param string $fields Datos nuevos de la colección en formato JSON
     * 
     * @return string Respuesta de éxito o fracaso
     */
    public function update_collection_data($collection, $fields)
    {
        $ch =  curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url.$collection.'.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        if( !is_null(json_decode($response)) ) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH" );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));
            $response = curl_exec($ch);
        }
    
        curl_close($ch);
    
        // Se convierte a Object o NULL
        return json_decode($response);
    }

    /**
     * Obtiene todos los datos de un documento
     * 
     * @param string $document Nombre del documento que se quiere leer
     * 
     * @return string|bool Respuesta decodificada como un json
     */
    public function read_document($document)
    {
        $ch =  curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url.$document.'.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        
        curl_close($ch);

        return json_decode($response);
    }

    /**
     * Obtiene los datos de una colección
     * 
     * @param string $document Nombre del documento al que pertenece la colección
     * 
     * @param string $collection Ruta o nombre de la colección que se quiere leer
     * 
     * @return string Respuesta decodificada como un json
     */
    public function read_collection($document, $collection)
    {
        $ch =  curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url.$document.'/'.$collection.'.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        
        curl_close($ch);

        return json_decode($response);
    }

    /**
     * Elimina un documento
     * 
     * @param string $document Nombre del documento que se quiere eliminar
     * 
     * @return boolean Respuesta de éxito o fracaso
     */
    public function delete_document($document)
    {
        $ch =  curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url.$document.'.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        // Si no se obtuvieron resultados, entonces no existe la colección
        if( is_null(json_decode($response)) ) {
            $resBool =  false;
        } else {    // Si existe la colección, entnces se procede a eliminar la colección
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE" ); 
            curl_exec($ch);
            $resBool =  true;
        }
        
        curl_close($ch);

        // Se devuelve true o false
        return $resBool;
    }

    /**
     * Elimina una collección especifica
     * 
     * @param string $document Nombre del documento al que pertenece la colección
     * 
     * @param string $collection Ruta de la colección que se quiere eliminar
     * 
     * @return boolean Respuesta de éxito o fracaso
     */
    public function delete_collection($document, $collection)
    {
        $ch =  curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url.$document.'/'.$collection.'.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        // Si no se obtuvieron resultados, entonces no existe la colección
        if( is_null(json_decode($response)) ) {
            $resBool =  false;
        } else {    // Si existe la colección, entnces se procede a eliminar la colección
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE" ); 
            curl_exec($ch);
            $resBool =  true;
        }
        
        curl_close($ch);

        // Se devuelve true o false
        return $resBool;
    }
}
