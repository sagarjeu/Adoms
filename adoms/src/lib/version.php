<?php declare (strict_types = 1);
namespace Adoms\src\lib;



class Version {
    public function about($vbool)  {
        if ($vbool == 0) {
            echo 'Adoms - (Helium) 1.0<br>';
            echo 'Adoms Obiented Library / PVC Model-View-Controller / Pipes Routing';
        }
        else if ($vbool == 1)
            echo 'Adoms v1';
        else
            for ($i = 0 ; $i < $vbool ; $i++)
                echo 'Was \$vbool too complex an idea for you? ... ';
    }
}
