<style>
    .team__posts-wrapper {
      display: flex;
      flex-wrap: wrap;
      padding-top: 4em;
      padding-bottom: 4em;
      max-width: 1366px;
      margin: 0 auto;
    }
    
    .team__item {
      max-width: 100%;
      flex: 0 0 100%;
      padding-left: 1em;
      padding-right: 1em;
      margin-bottom: 4em;
      text-align: center;
    }
    
    .team__image-group {
      position: relative;
      margin: 0 auto;
      width: 100%;
      aspect-ratio: 1 / 1;
      overflow: hidden;
      border-radius: 20px;
      border: solid 2px black;
    }
    
    .team__main-image,
    .team__alt-image {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-position: center;
    }
    
    .team__main-image {
      object-fit: cover;
      transition: filter 300ms;
    }
    
    .team__alt-image {
      object-fit: contain;
    }
    
    .team__alt-image {
      transition: opacity 300ms;
      opacity: 0;
    }
    
    .team__item:hover .team__main-image {
      filter: blur(1.5rem);
    }
    
    .team__item:hover .team__alt-image {
      opacity: 100%;
    }
    
    .team__content {
      font-size: 1.25em;
      font-weight: 500;
    }
    
    .team__permalink {
      margin-top: 1em;
      display: block;
      text-align: center;
    }
    
    .team__permalink:hover {
      text-decoration: underline;
    }
    
    .single-team__main-image {
      position: relative;
      margin: 0 auto;
      width: 300px;
      height: auto;
      aspect-ratio: 1/1;
      max-width: 100%;
      border-radius: 20px;
      border: solid 2px black;
      object-fit: cover;
      object-position: center;
    }
    
    .single-team__wrapper {
      padding: 4em 0.5em;
      max-width: 991px;
      margin: 0 auto;
    }
    
    .single-team__content {
      font-size: 1.5rem;
      font-weight: 500;
      margin-bottom: 2em;
    }
    
    .single-team__back {
      font-size: 1.25rem;
    }
    
    .single-team__back:hover {
      text-decoration: underline;
    }
    
    @media (min-width: 576px) {
      .team__image-group {
          width: 301px;
          height: 301px;
      }
    }
    
    @media (min-width: 991px) {
      .team__item {
          max-width: 33%;
          flex: 0 0 33%;
      }
    }
    </style>