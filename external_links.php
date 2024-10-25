<?php
$externalLinks = [
    'Hurghada, Egypt' => 'https://christiantour.ro/sejur-oferte-vacante/egipt/hurghada/plecare-din_oradea_camere_%5B%7B%22adults%22%3A%222%22%7D%5D_transport_avion_ordoneaza_dupa-recomandate-descrescator/1',
    
    "Sharm El Sheikh, Egypt" => "https://christiantour.ro/sejur-oferte-vacante/egipt/sharm-el-sheikh/plecare-din_cluj-napoca_camere_%5B%7B%22adults%22%3A%222%22%7D%5D_transport_avion_ordoneaza_dupa-recomandate-descrescator/1",

    "Corfu, Greece" => "https://christiantour.ro/sejur-oferte-vacante/grecia/corfu/plecare-din_timisoara_camere_%5B%7B%22adults%22%3A%222%22%7D%5D_transport_avion_ordoneaza_dupa-recomandate-descrescator/1",

    "Zakynthos, Greece" => "https://christiantour.ro/sejur-oferte-vacante/grecia/zakynthos-001/camere_%5B%7B%22adults%22%3A%222%22%7D%5D_transport_avion_ordoneaza_dupa-recomandate-descrescator/1",

    "Rhodes, Greece" => "https://christiantour.ro/sejur-oferte-vacante/grecia/rodos/camere_%5B%7B%22adults%22%3A%222%22%7D%5D_transport_avion_plecare-din_cluj-napoca_ordoneaza_dupa-recomandate-descrescator/1",

    "Crete, Greece" => "https://christiantour.ro/sejur-oferte-vacante/grecia/creta-rethymno-si-chania/camere_%5B%7B%22adults%22%3A%222%22%7D%5D_transport_avion_plecare-din_oradea_ordoneaza_dupa-recomandate-descrescator/1",

    "Kusadasi, Turkey" => "https://christiantour.ro/sejur-oferte-vacante/turcia/kusadasi/camere_%5B%7B%22adults%22%3A%222%22%7D%5D_transport_avion_plecare-din_bucuresti_ordoneaza_dupa-recomandate-descrescator/1",

    "Antalya, Turkey" => "https://christiantour.ro/sejur-oferte-vacante/turcia/antalya/camere_%5B%7B%22adults%22%3A%222%22%7D%5D_transport_avion_plecare-din_oradea_ordoneaza_dupa-recomandate-descrescator/1",

    "Bodrum, Turkey" => "https://christiantour.ro/sejur-oferte-vacante/turcia/bodrum/camere_%5B%7B%22adults%22%3A%222%22%7D%5D_transport_avion_plecare-din_bucuresti_ordoneaza_dupa-recomandate-descrescator/1",

];   

// Funcție pentru a obține linkul extern pentru o destinație dată
function getExternalLink($destinationName) {
    global $externalLinks; // Asigură-te că ai acces la array-ul global
    return $externalLinks[$destinationName] ?? '#'; // Întoarce '#' dacă nu găsește linkul
}
?>
