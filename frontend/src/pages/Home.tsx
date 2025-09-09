import React from 'react';
import { Layout } from '@/components/layout/Layout';
import TopFiveSongs from '@/components/TopFiveSongs';
import OtherSongs from '@/components/OtherSongs';
import SuggestionForm from '@/components/SuggestionForm';
import { Song } from '@/types';

export const Home: React.FC = () => {
  const handleSongPlay = (song: Song) => {
    // Open YouTube in a new tab
    window.open(song.youtube_url, '_blank', 'noopener,noreferrer');
  };

  return (
    <Layout>
      <div className="space-y-0">
        {/* Hero Section with Top 5 */}
        <TopFiveSongs onSongPlay={handleSongPlay} />
        
        {/* Other Songs Section */}
        <OtherSongs onSongPlay={handleSongPlay} />
        
        {/* Suggestion Form Section */}
        <SuggestionForm />
      </div>
    </Layout>
  );
};

export default Home;