/**
 * Generate a video thumbnail when a new video is uploaded to the media library.
 */
add_action('add_attachment', 'generate_video_thumbnail_on_add_attachment');

function generate_video_thumbnail_on_add_attachment($attachment_id) {
    $mime_type = get_post_mime_type($attachment_id);

    // Only process video files
    if (strpos($mime_type, 'video/') !== 0) {
        return;
    }

    // Avoid duplicate thumbnail creation
    if (get_post_meta($attachment_id, '_video_thumbnail_id', true)) {
        return;
    }

    $thumb_id = generate_video_thumbnail_from_attachment($attachment_id);

    if (!is_wp_error($thumb_id)) {
        update_post_meta($attachment_id, '_video_thumbnail_id', $thumb_id);
    }
}

/**
 * Generate thumbnail image from video file.
 */
function generate_video_thumbnail_from_attachment($attachment_id) {
    $video_path = get_attached_file($attachment_id);

    if (!file_exists($video_path)) {
        return new WP_Error('file_not_found', 'Video file not found locally.');
    }

    $video_name = pathinfo($video_path, PATHINFO_FILENAME);
    $thumbnail_filename = $video_name . '_thumbnail.jpg';

    $upload_dir     = wp_upload_dir();
    $thumbnail_path = trailingslashit($upload_dir['path']) . $thumbnail_filename;
    $thumbnail_url  = trailingslashit($upload_dir['url']) . $thumbnail_filename;

    // Generate thumbnail using FFmpeg
    $cmd = "ffmpeg -y -ss 00:00:01 -i " . escapeshellarg($video_path) . " -vframes 1 " . escapeshellarg($thumbnail_path) . " 2>&1";
    shell_exec($cmd);

    if (!file_exists($thumbnail_path)) {
        return new WP_Error('thumbnail_failed', 'Failed to create thumbnail.');
    }

    // Insert thumbnail as a media attachment
    $attachment = [
        'guid'           => $thumbnail_url,
        'post_mime_type' => 'image/jpeg',
        'post_title'     => sanitize_file_name($thumbnail_filename),
        'post_content'   => '',
        'post_status'    => 'inherit'
    ];

    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $attach_id = wp_insert_attachment($attachment, $thumbnail_path);

    if (is_wp_error($attach_id)) {
        unlink($thumbnail_path);
        return $attach_id;
    }

    $attach_data = wp_generate_attachment_metadata($attach_id, $thumbnail_path);
    wp_update_attachment_metadata($attach_id, $attach_data);

    return $attach_id;
}
