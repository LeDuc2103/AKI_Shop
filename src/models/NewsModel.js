/**
 * News Model - Quản lý dữ liệu tin tức
 */
class NewsModel {
    constructor() {
        this.articles = [];
    }

    /**
     * Lấy danh sách tin tức mới nhất
     */
    getLatestNews() {
        return [
            {
                id: 1,
                title: 'Review "Lễ Phải Của Phi Lý Trí": Khi Cảm Xúc Và Phi Logic Dẫn Dướng',
                excerpt: 'Bạn đã bao giờ đưa ra một quyết định "ngớ ngẩn" nhưng lại bất ngờ gặt hái kết quả tốt hơn mong đợi chưa? Nếu có, thì bạn đã trải nghiệm chính xác những gì mà...',
                image: 'assets/images/bn-1.png',
                date: '15/08/2025',
                views: 17,
                category: 'review',
                author: 'AKI Team'
            },
            {
                id: 2,
                title: 'Savi 6 vs Boox Go 6: Khác biệt gì ở phiên bản được tinh chỉnh riêng cho người Việt?',
                excerpt: 'Với những người yêu thích sách, việc lựa chọn một thiết bị phù hợp với nhu cầu cá nhân ngày càng trở nên quan trọng. Trong số các lựa chọn nổi bật hiện nay...',
                image: 'assets/images/product/boox_go_7.png',
                date: '14/08/2025',
                views: 33,
                category: 'comparison',
                author: 'Tech Expert'
            },
            {
                id: 3,
                title: 'Review "Khởi Nghiệp Thông Minh" của TS. Ngô Công Trường: Khởi nghiệp bài bản, giảm rủi ro',
                excerpt: 'Trong bối cảnh kinh tế đầy biến động, việc khởi nghiệp cần có phương pháp bài bản hơn là "liều một phát ăn ngay". Cuốn sách "Khởi Nghiệp Thông Minh" của...',
                image: 'assets/images/banner/homepage1.png',
                date: '13/08/2025',
                views: 12,
                category: 'review',
                author: 'Business Analyst'
            }
        ];
    }

    /**
     * Lấy tin tức theo category
     */
    getNewsByCategory(category) {
        const allNews = this.getLatestNews();
        if (category === 'all') return allNews;
        return allNews.filter(article => article.category === category);
    }

    /**
     * Lấy tin tức theo ID
     */
    getNewsById(id) {
        const allNews = this.getLatestNews();
        return allNews.find(article => article.id === id);
    }

    /**
     * Tìm kiếm tin tức
     */
    searchNews(keyword) {
        const allNews = this.getLatestNews();
        return allNews.filter(article => 
            article.title.toLowerCase().includes(keyword.toLowerCase()) ||
            article.excerpt.toLowerCase().includes(keyword.toLowerCase())
        );
    }

    /**
     * Lấy tin tức liên quan
     */
    getRelatedNews(currentId, category, limit = 3) {
        const allNews = this.getLatestNews();
        return allNews
            .filter(article => article.id !== currentId && article.category === category)
            .slice(0, limit);
    }
}
