<?php

namespace App\Helpers;

class YouTubeHelper
{
    /**
     * Regex pattern for YouTube URLs with support for various formats
     */
    public const URL_PATTERN = '/^https?:\/\/(?:(?:www|m)\.)?(?:youtube\.com\/(?:watch\?.*v=|embed\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})(?:\S+)?$/';

    /**
     * Extract video ID from YouTube URL
     * 
     * @param string $url
     * @return string|null
     */
    public static function extractVideoId(string $url): ?string
    {
        // Handle different YouTube URL formats
        $patterns = [
            '/(?:youtube\.com\/watch\?.*v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/v\/)([a-zA-Z0-9_-]{11})/',
            '/^([a-zA-Z0-9_-]{11})$/' // Direct video ID
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Normalize YouTube URL to standard format
     * 
     * @param string $url
     * @return string|null
     */
    public static function normalizeUrl(string $url): ?string
    {
        $videoId = self::extractVideoId($url);
        
        if (!$videoId) {
            return null;
        }

        return "https://www.youtube.com/watch?v={$videoId}";
    }

    /**
     * Validate if URL is a valid YouTube URL
     * 
     * @param string $url
     * @return bool
     */
    public static function isValidUrl(string $url): bool
    {
        return self::extractVideoId($url) !== null;
    }

    /**
     * Get thumbnail URL for YouTube video
     * 
     * @param string $url
     * @param string $quality
     * @return string|null
     */
    public static function getThumbnailUrl(string $url, string $quality = 'hqdefault'): ?string
    {
        $videoId = self::extractVideoId($url);
        
        if (!$videoId) {
            return null;
        }

        return "https://img.youtube.com/vi/{$videoId}/{$quality}.jpg";
    }

    /**
     * Get embed URL for YouTube video
     * 
     * @param string $url
     * @return string|null
     */
    public static function getEmbedUrl(string $url): ?string
    {
        $videoId = self::extractVideoId($url);
        
        if (!$videoId) {
            return null;
        }

        return "https://www.youtube.com/embed/{$videoId}";
    }

    /**
     * Supported URL formats for reference
     * 
     * @return array
     */
    public static function getSupportedFormats(): array
    {
        return [
            'https://www.youtube.com/watch?v=VIDEO_ID',
            'https://youtube.com/watch?v=VIDEO_ID',
            'https://m.youtube.com/watch?v=VIDEO_ID',
            'https://youtu.be/VIDEO_ID',
            'https://www.youtube.com/watch?v=VIDEO_ID&list=PLAYLIST',
            'https://www.youtube.com/watch?v=VIDEO_ID&t=30s',
            'https://youtu.be/VIDEO_ID?si=SHARE_PARAM',
            'https://www.youtube.com/embed/VIDEO_ID',
            'https://www.youtube.com/v/VIDEO_ID',
        ];
    }
}