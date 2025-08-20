/**
 * Product Model - Quản lý dữ liệu sản phẩm
 */
class ProductModel {
    constructor() {
        this.products = [];
        this.accessories = [];
        this.categories = [];
    }

    /**
     * Lấy danh sách sản phẩm hot sale
     */
    getHotSaleProducts() {
        return [
            {
                id: 1,
                name: "Boox Go 7 (2024)",
                image: "assets/images/product/boox_go_7.png",
                currentPrice: "4.990.000 đ",
                oldPrice: "Giá NY: 5.990.000 đ",
                discount: "-17%",
                rating: 5,
                category: "boox"
            },
            {
                id: 2,
                name: "Kindle Paperwhite 5 (2021)",
                image: "assets/images/product/boox_go_7.png",
                currentPrice: "3.290.000 đ",
                oldPrice: "Giá NY: 3.990.000 đ",
                discount: "-18%",
                rating: 5,
                category: "kindle"
            },
            {
                id: 3,
                name: "Kobo Libra 2",
                image: "assets/images/product/boox_go_7.png",
                currentPrice: "4.890.000 đ",
                oldPrice: "Giá NY: 5.490.000 đ",
                discount: "-11%",
                rating: 5,
                category: "kobo"
            },
            {
                id: 4,
                name: "reMarkable 2",
                image: "assets/images/product/boox_go_7.png",
                currentPrice: "8.990.000 đ",
                oldPrice: "Giá NY: 9.990.000 đ",
                discount: "-10%",
                rating: 5,
                category: "remarkable"
            },
            {
                id: 5,
                name: "Boox Note Air 2 Plus",
                image: "assets/images/product/boox_go_7.png",
                currentPrice: "12.990.000 đ",
                oldPrice: "Giá NY: 14.990.000 đ",
                discount: "-13%",
                rating: 5,
                category: "boox"
            }
        ];
    }

    /**
     * Lấy danh sách phụ kiện
     */
    getAccessories() {
        return [
            {
                id: 101,
                name: "Bao da Kindle Paperwhite 5 (2021)",
                image: "assets/images/product/boox_go_7.png",
                currentPrice: "450.000 đ",
                oldPrice: "Giá NY: 530.000 đ",
                discount: "-15%",
                rating: 5
            },
            {
                id: 102,
                name: "Bút Boox Pen Plus",
                image: "assets/images/product/boox_go_7.png",
                currentPrice: "1.790.000 đ",
                oldPrice: "Giá NY: 1.990.000 đ",
                discount: "-10%",
                rating: 5
            },
            {
                id: 103,
                name: "Film bảo vệ màn hình Kobo Libra 2",
                image: "assets/images/product/boox_go_7.png",
                currentPrice: "159.000 đ",
                oldPrice: "Giá NY: 199.000 đ",
                discount: "-20%",
                rating: 5
            }
        ];
    }

    /**
     * Lấy sản phẩm theo category
     */
    getProductsByCategory(category) {
        const allProducts = this.getHotSaleProducts();
        if (category === 'all') return allProducts;
        return allProducts.filter(product => product.category === category);
    }

    /**
     * Lấy sản phẩm theo ID
     */
    getProductById(id) {
        const allProducts = [...this.getHotSaleProducts(), ...this.getAccessories()];
        return allProducts.find(product => product.id === id);
    }

    /**
     * Tìm kiếm sản phẩm
     */
    searchProducts(keyword) {
        const allProducts = [...this.getHotSaleProducts(), ...this.getAccessories()];
        return allProducts.filter(product => 
            product.name.toLowerCase().includes(keyword.toLowerCase())
        );
    }
}
