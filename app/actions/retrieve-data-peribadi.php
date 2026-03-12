<?php

  $namaPenuh = $profileView['nama_penuh'] ?? '';
  $nickname  = $profileView['nickname']   ?? '';
  $jawatan   = $profileView['jawatan']    ?? '';
  $gred      = $profileView['gred']       ?? '';
  $jabatan   = $profileView['jabatan']    ?? '';
  $stafID    = $profileView['stafID']     ?? '';
  $nopek     = $profileView['nopekerja']  ?? '';
  $emel      = $profileView['emel']       ?? '';
  $jawGred   = trim($jawatan . ($gred ? ' • '.$gred : ''));
  
  
?>