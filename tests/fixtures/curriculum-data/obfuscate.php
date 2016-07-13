#!/usr/bin/env php
<?php

$outFile = __DIR__ . '/lp21_curriculum_obfuscated.xml';
$inFile  = __DIR__ . '/lp21_curriculum.xml';

// Reset the file contents.
file_put_contents($outFile, '');

// Open both files.
$out = fopen($outFile, 'a');
$in  = fopen($inFile, 'r');

// Read line by line, and obfuscate all "sensitive" data.
$uuids = array();
$texts = array();
$codes = array();
while (($line = fgets($in)) !== false) {
    // Ignore lines.
    if (preg_match('/^<(mindestanspruch|orientierungspunkt|spaeter_im_zyklus|orientierungspunkt_vorher|linie_oben|linie_unten|anzahl_in_zyklus|anzahl_in_kompetenz|folge_in_aufbaute)>/', trim($line))) {
        continue;
    }

    // Replace UUIDs.
    $match;
    if (preg_match('/uuid="(\w+)/', $line, $match)) {
        $withoutVersionPrefix = substr($match[1], 3);
        if (!isset($uuids[$withoutVersionPrefix])) {
            $uuids[$withoutVersionPrefix] = 'OBF-' . sha1(uniqid());
        }
        $line = str_replace($match[1], $uuids[$withoutVersionPrefix], $line);
    }

    // Replace URLs.
    $match;
    if (preg_match('/<url>https?:\/\/(\w+)\.lehrplan.ch\/(.+)<\/url>/', $line, $match)) {
        $withoutVersionPrefix = substr($match[2], 3);
        if (!isset($uuids[$withoutVersionPrefix])) {
            $uuids[$withoutVersionPrefix] = 'OBF-' . sha1(uniqid());
        }
        $line = str_replace($match[0], '<url>http://' . $match[1] . '.example.com/' . $uuids[$withoutVersionPrefix] . '</url>', $line);
    }

    // Replace codes.
    $match;
    if (preg_match('/<code>(.+?)<\/code>/', $line, $match)) {
        if (!isset($uuids[$match[1]])) {
            $uuids[$match[1]] = 'OBF-' . uniqid();
        }
        $line = str_replace($match[1], $uuids[$match[1]], $line);
    }

    // Remove IDs that start with a number.
    $line = preg_replace('/\sid="\d.*?"/', '', $line);

    // If this is a text line, replace all the text.
    $match;
    if (preg_match('/^<de>(.+?)<\/de>/', trim($line), $match)) {
        if (!isset($texts[$match[1]])) {
            $wordCount = count(explode(' ', $match[1]));
            $text = devel_create_greeking($wordCount, $wordCount < 3);
            $texts[$match[1]] = $text;
        }
        $line = preg_replace('/<de>.+?<\/de>/', "<de>{$texts[$match[1]]}</de>", $line);
    }

    fwrite($out, $line);
}

// Close the XML files.
fclose($in);
fclose($out);

echo "Done with the XML files. Moving on to the ASCII dumps...\n";

// Replace all UUIDs in the ascii files.
foreach ([
    __DIR__ . '/lp21_curriculum_obfuscated.ascii' => __DIR__ . '/lp21_curriculum.ascii',
    __DIR__ . '/lp21_taxonomy_tree_obfuscated.ascii' => __DIR__ . '/lp21_taxonomy_tree.ascii',
] as $outFile => $inFile) {
    // Reset the file contents.
    file_put_contents($outFile, '');

    // Open both files.
    $out = fopen($outFile, 'a');
    $in  = fopen($inFile, 'r');

    // Read all lines, replacing UUIDs as we go.
    while (($line = fgets($in)) !== false) {
        // Replace UUIDs.
        $match;
        if (preg_match('/:(\w+)/', $line, $match)) {
            $withoutVersionPrefix = substr($match[1], 3);
            if (isset($uuids[$withoutVersionPrefix])) {
                $line = str_replace($match[1], $uuids[$withoutVersionPrefix], $line);
            }
        }

        fwrite($out, $line);
    }

    // Close the ASCII files.
    fclose($in);
    fclose($out);
}

echo "Done with the ASCII dumps. Moving on to the JSON example files...\n";

// Replace all UUIDs in the JSON files.
$outFile = __DIR__ . '/lp21_taxonomy_tree_obfuscated.json';
$inFile  = __DIR__ . '/lp21_taxonomy_tree.json';

// Reset the file contents.
file_put_contents($outFile, '');

// Open both files.
$out = fopen($outFile, 'a');
$in  = fopen($inFile, 'r');

// Read all lines, replacing UUIDs and text as we go.
while (($line = fgets($in)) !== false) {
    // Replace UUIDs.
    $match;
    if (preg_match('/"id":\s?"(\w+)/', $line, $match)) {
        $withoutVersionPrefix = substr($match[1], 3);
        if (isset($uuids[$withoutVersionPrefix])) {
            $line = str_replace($match[1], $uuids[$withoutVersionPrefix], $line);
        }
    }

    // Replace texts.
    $match;
    if (preg_match('/"de":\s?"(.+?)"/', $line, $match)) {
        if (isset($texts[$match[1]])) {
            $line = str_replace($match[1], $texts[$match[1]], $line);
        }
    }

    fwrite($out, $line);
}

// Finish.
echo "Finished.\n";


/**
 * Verbatim copy from the Devel Drupal module.
 */
function devel_create_greeking($word_count, $title = FALSE) {
  $dictionary = array("abbas", "abdo", "abico", "abigo", "abluo", "accumsan",
    "acsi", "ad", "adipiscing", "aliquam", "aliquip", "amet", "antehabeo",
    "appellatio", "aptent", "at", "augue", "autem", "bene", "blandit",
    "brevitas", "caecus", "camur", "capto", "causa", "cogo", "comis",
    "commodo", "commoveo", "consectetuer", "consequat", "conventio", "cui",
    "damnum", "decet", "defui", "diam", "dignissim", "distineo", "dolor",
    "dolore", "dolus", "duis", "ea", "eligo", "elit", "enim", "erat",
    "eros", "esca", "esse", "et", "eu", "euismod", "eum", "ex", "exerci",
    "exputo", "facilisi", "facilisis", "fere", "feugiat", "gemino",
    "genitus", "gilvus", "gravis", "haero", "hendrerit", "hos", "huic",
    "humo", "iaceo", "ibidem", "ideo", "ille", "illum", "immitto",
    "importunus", "imputo", "in", "incassum", "inhibeo", "interdico",
    "iriure", "iusto", "iustum", "jugis", "jumentum", "jus", "laoreet",
    "lenis", "letalis", "lobortis", "loquor", "lucidus", "luctus", "ludus",
    "luptatum", "macto", "magna", "mauris", "melior", "metuo", "meus",
    "minim", "modo", "molior", "mos", "natu", "neo", "neque", "nibh",
    "nimis", "nisl", "nobis", "nostrud", "nulla", "nunc", "nutus", "obruo",
    "occuro", "odio", "olim", "oppeto", "os", "pagus", "pala", "paratus",
    "patria", "paulatim", "pecus", "persto", "pertineo", "plaga", "pneum",
    "populus", "praemitto", "praesent", "premo", "probo", "proprius",
    "quadrum", "quae", "qui", "quia", "quibus", "quidem", "quidne", "quis",
    "ratis", "refero", "refoveo", "roto", "rusticus", "saepius",
    "sagaciter", "saluto", "scisco", "secundum", "sed", "si", "similis",
    "singularis", "sino", "sit", "sudo", "suscipere", "suscipit", "tamen",
    "tation", "te", "tego", "tincidunt", "torqueo", "tum", "turpis",
    "typicus", "ulciscor", "ullamcorper", "usitas", "ut", "utinam",
    "utrum", "uxor", "valde", "valetudo", "validus", "vel", "velit",
    "veniam", "venio", "vereor", "vero", "verto", "vicis", "vindico",
    "virtus", "voco", "volutpat", "vulpes", "vulputate", "wisi", "ymo",
    "zelus");
  $dictionary_flipped = array_flip($dictionary);

  $greeking = '';

  if (!$title) {
    $words_remaining = $word_count;
    while ($words_remaining > 0) {
      $sentence_length = mt_rand(3, 10);
      $words = array_rand($dictionary_flipped, $sentence_length);
      $sentence = implode(' ', $words);
      $greeking .= ucfirst($sentence) . '. ';
      $words_remaining -= $sentence_length;
    }
  }
  else {
    // Use slightly different method for titles.
    $words = array_rand($dictionary_flipped, $word_count);
    $words = is_array($words) ? implode(' ', $words) : $words;
    $greeking = ucwords($words);
  }

  // Work around possible php garbage collection bug. Without an unset(), this
  // function gets very expensive over many calls (php 5.2.11).
  unset($dictionary, $dictionary_flipped);
  return trim($greeking);
}
