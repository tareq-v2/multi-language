import React from 'react';

const PlaylistResult = ({ playlist }) => {
  return (
    <div className="playlist-result">
      <h3>Recommended Playlist:</h3>
      <div className="spotify-embed">
        <iframe
          src={playlist}
          width="100%"
          height="380"
          frameBorder="0"
          allow="encrypted-media"
          title="Recommended playlist"
        ></iframe>
      </div>
    </div>
  );
};

export default PlaylistResult;
