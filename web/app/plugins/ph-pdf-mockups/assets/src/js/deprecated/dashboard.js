const ns = "ph-pdf-mockups";

// process pdf page as featured media
import featuredMedia from "./image/featured-media-shortcode";
ph.hooks.addAction("ph.image.getFeaturedMedia", ns, featuredMedia);
