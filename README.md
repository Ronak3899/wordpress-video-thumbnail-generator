# WordPress Video Thumbnail Generator

Automatically generates a thumbnail image from uploaded video files using FFmpeg. The thumbnail is saved as a media attachment and linked to the video using post meta.

## Features

- Automatically runs when a video is uploaded to the WordPress media library.
- Uses `ffmpeg` to extract a frame at 1 second.
- Stores the generated thumbnail as a separate media item.
- Saves the thumbnail ID as `_video_thumbnail_id` post meta on the original video attachment.

## Requirements

- **WordPress**
- **PHP**
- **FFmpeg installed on the server** and available in shell environment

## Installation

1. Ensure FFmpeg is installed and accessible via shell (check with `ffmpeg -version`).
2. Copy the PHP code into your theme's `functions.php` or create a custom plugin.
3. Upload a video file to the WordPress Media Library.
4. The thumbnail will be automatically generated.

## Usage

- The generated thumbnail is stored as a WordPress media attachment.
- Its ID is saved in the meta field `_video_thumbnail_id` of the original video attachment.
- You can retrieve it using:

```php
$thumbnail_id = get_post_meta($video_id, '_video_thumbnail_id', true);
$thumbnail_url = wp_get_attachment_url($thumbnail_id);
