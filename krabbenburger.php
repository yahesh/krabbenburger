#!/usr/bin/env php
<?php

  # set the access token for the Mastodon instance
  define("INSTANCE_TOKEN", "!!! ADD A MASTODON ACCESS TOKEN HERE !!!");

  # set the URL of the Mastodon instance
  define("INSTANCE_URL", "https://chaos.social");

  # set the description of the image
  define("MEDIA_DESCRIPTION", "Patrick Star liegt in seinem Bett und isst einen Krabbenburger.");

  # set the filename of the image
  define("MEDIA_FILE", __DIR__."/krabbenburger.gif");

  # set the visibility of the status
  define("STATUS_LANGUAGE", "de");

  # set the text of the status
  define("STATUS_TEXT", "Oh Kinder, 3 Uhr morgens! Zeit für einen Krabbenburger!");

  # set the visibility of the status:
  # public, unlisted, private, direct
  define("STATUS_VISIBILITY", "public");

  # ===== DO NOT EDIT BELOW THIS LINE =====

  function callMastodonAPI($content, $url) {
    $result = false;

    $curl = curl_init($url);
    if (false !== $curl) {
      $body   = false;
      $header = false;

      try {
        # configure cURL
        curl_setopt($curl, CURLOPT_HTTPHEADER,     ["Authorization: Bearer ".INSTANCE_TOKEN, "Content-Type: multipart/form-data"]);
        curl_setopt($curl, CURLOPT_POST,           1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,     $content);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        # execute cURL
        $body   = curl_exec($curl);
        $header = curl_getinfo($curl);
      } finally {
        curl_close($curl);
      }

      # check the result
      if ((false !== $body) && (false !== $header)) {
        if (200 === $header["http_code"]) {
          $result = json_decode($body, true, 512, JSON_OBJECT_AS_ARRAY);
        }
      }
    }

    return $result;
  }

  function main($arguments) {
    $result = 1;

    # send the media file
    $media    = ["description" => MEDIA_DESCRIPTION,
                 "file"        => curl_file_create(MEDIA_FILE, mime_content_type(MEDIA_FILE), basename(MEDIA_FILE))];
    $response = callMastodonAPI($media, INSTANCE_URL."/api/v1/media");

    # check if we got a positive response including a media ID
    if (is_array($response) && array_key_exists("id", $response)) {
      # send the status
      $status = ["language"    => STATUS_LANGUAGE,
                 "media_ids[]" => $response["id"],
                 "status"      => STATUS_TEXT,
                 "visibility"  => STATUS_VISIBILITY];
      $response = callMastodonAPI($status, INSTANCE_URL."/api/v1/statuses");

      # check if we were successful
      if (false !== $response) {
        $result = 0;
      }
    }
    
    return $result;
  }

  exit(main($argv));

