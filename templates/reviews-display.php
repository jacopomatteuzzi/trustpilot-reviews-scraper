<?php
if (!defined('ABSPATH')) {
    exit;
}

$style = $atts['style'];
$show_rating = filter_var($atts['show_rating'], FILTER_VALIDATE_BOOLEAN);
$show_date = filter_var($atts['show_date'], FILTER_VALIDATE_BOOLEAN);
$show_author = filter_var($atts['show_author'], FILTER_VALIDATE_BOOLEAN);
?>

<div class="trs-reviews-container trs-style-<?php echo esc_attr($style); ?>">
    <?php if ($style === 'carousel'): ?>
        <!-- Carosello -->
        <div class="trs-carousel">
            <div class="trs-carousel-wrapper">
                <?php foreach ($reviews as $review): ?>
                    <div class="trs-review-item">
                        <div class="trs-review-content">
                            <?php if ($show_rating): ?>
                                <div class="trs-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="trs-star <?php echo $i <= $review->rating ? 'filled' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($review->title): ?>
                                <h4 class="trs-review-title"><?php echo esc_html($review->title); ?></h4>
                            <?php endif; ?>
                            
                            <div class="trs-review-text">
                                <?php echo wp_kses_post($review->content); ?>
                            </div>
                            
                            <div class="trs-review-meta">
                                <?php if ($show_author): ?>
                                    <span class="trs-author"><?php echo esc_html($review->author); ?></span>
                                <?php endif; ?>
                                
                                <?php if ($show_date): ?>
                                    <span class="trs-date"><?php echo date_i18n(get_option('date_format'), strtotime($review->date)); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <button class="trs-carousel-nav trs-prev">‹</button>
            <button class="trs-carousel-nav trs-next">›</button>
        </div>
        
    <?php elseif ($style === 'gallery'): ?>
        <!-- Galleria -->
        <div class="trs-gallery">
            <?php foreach ($reviews as $review): ?>
                <div class="trs-gallery-item">
                    <div class="trs-review-content">
                        <?php if ($show_rating): ?>
                            <div class="trs-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="trs-star <?php echo $i <= $review->rating ? 'filled' : ''; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($review->title): ?>
                            <h4 class="trs-review-title"><?php echo esc_html($review->title); ?></h4>
                        <?php endif; ?>
                        
                        <div class="trs-review-text">
                            <?php echo wp_kses_post($review->content); ?>
                        </div>
                        
                        <div class="trs-review-meta">
                            <?php if ($show_author): ?>
                                <span class="trs-author"><?php echo esc_html($review->author); ?></span>
                            <?php endif; ?>
                            
                            <?php if ($show_date): ?>
                                <span class="trs-date"><?php echo date_i18n(get_option('date_format'), strtotime($review->date)); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
    <?php elseif ($style === 'masonry'): ?>
        <!-- Masonry -->
        <div class="trs-masonry">
            <?php foreach ($reviews as $review): ?>
                <div class="trs-masonry-item">
                    <div class="trs-review-content">
                        <?php if ($show_rating): ?>
                            <div class="trs-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="trs-star <?php echo $i <= $review->rating ? 'filled' : ''; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($review->title): ?>
                            <h4 class="trs-review-title"><?php echo esc_html($review->title); ?></h4>
                        <?php endif; ?>
                        
                        <div class="trs-review-text">
                            <?php echo wp_kses_post($review->content); ?>
                        </div>
                        
                        <div class="trs-review-meta">
                            <?php if ($show_author): ?>
                                <span class="trs-author"><?php echo esc_html($review->author); ?></span>
                            <?php endif; ?>
                            
                            <?php if ($show_date): ?>
                                <span class="trs-date"><?php echo date_i18n(get_option('date_format'), strtotime($review->date)); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
    <?php else: ?>
        <!-- Grid -->
        <div class="trs-grid">
            <?php foreach ($reviews as $review): ?>
                <div class="trs-grid-item">
                    <div class="trs-review-content">
                        <?php if ($show_rating): ?>
                            <div class="trs-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="trs-star <?php echo $i <= $review->rating ? 'filled' : ''; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($review->title): ?>
                            <h4 class="trs-review-title"><?php echo esc_html($review->title); ?></h4>
                        <?php endif; ?>
                        
                        <div class="trs-review-text">
                            <?php echo wp_kses_post($review->content); ?>
                        </div>
                        
                        <div class="trs-review-meta">
                            <?php if ($show_author): ?>
                                <span class="trs-author"><?php echo esc_html($review->author); ?></span>
                            <?php endif; ?>
                            
                            <?php if ($show_date): ?>
                                <span class="trs-date"><?php echo date_i18n(get_option('date_format'), strtotime($review->date)); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div> 