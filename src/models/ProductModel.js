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
            },
            {
                id: 104,
                name: "Bao da reMarkable 2 Book Folio",
                image: "assets/images/product/boox_go_7.png",
                currentPrice: "1.890.000 đ",
                oldPrice: "Giá NY: 1.990.000 đ",
                discount: "-5%",
                rating: 5
            },
            {
                id: 105,
                name: "Bút Stylus Wacom One",
                image: "assets/images/product/boox_go_7.png",
                currentPrice: "1.290.000 đ",
                oldPrice: "Giá NY: 1.470.000 đ",
                discount: "-12%",
                rating: 5
            },
            {
                id: 106,
                name: "Adapter sạc Type C 30W",
                image: "assets/images/product/boox_go_7.png",
                currentPrice: "350.000 đ",
                oldPrice: "Giá NY: 380.000 đ",
                discount: "-8%",
                rating: 5
            },
            {
                id: 107,
                name: "AkiSkin Kindle Paperwhite - Galaxy",
                image: "assets/images/product/boox_go_7.png",
                currentPrice: "210.000 đ",
                oldPrice: "Giá NY: 250.000 đ",
                discount: "-15%",
                rating: 5
            },
            {
                id: 108,
                name: "Bao da Boox Note Air 2 chính hãng",
                image: "assets/images/product/boox_go_7.png",
                currentPrice: "1.350.000 đ",
                oldPrice: "Giá NY: 1.500.000 đ",
                discount: "-10%",
                rating: 5
            },
            {
                id: 109,
                name: "Bộ Tips thay thế cho bút reMarkable Marker",
                image: "assets/images/product/boox_go_7.png",
                currentPrice: "320.000 đ",
                oldPrice: "Giá NY: 400.000 đ",
                discount: "-20%",
                rating: 5
            },
            {
                id: 110,
                name: "Cáp sạc USB-C 100W siêu bền",
                image: "assets/images/product/boox_go_7.png",
                currentPrice: "190.000 đ",
                oldPrice: "Giá NY: 200.000 đ",
                discount: "-5%",
                rating: 5
            },
            {
                id: 111,
                name: "AkiSkin Kobo Libra 2 - Mountain",
                image: "assets/images/product/boox_go_7.png",
                currentPrice: "225.000 đ",
                oldPrice: "Giá NY: 250.000 đ",
                discount: "-10%",
                rating: 5
            },
            {
                id: 112,
                name: "Túi chống sốc 10 inch cho máy đọc sách",
                image: "assets/images/product/boox_go_7.png",
                currentPrice: "245.000 đ",
                oldPrice: "Giá NY: 300.000 đ",
                discount: "-18%",
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
     * Lấy sản phẩm theo brand
     */
    getProductsByBrand(brand) {
        const brandProducts = {
            'boox': [
                {
                    id: 201,
                    name: "Boox Go 7 (2024)",
                    image: "assets/images/product/boox_go_7.png",
                    currentPrice: "4.990.000 đ",
                    oldPrice: "Giá NY: 5.990.000 đ",
                    discount: "-17%",
                    rating: 5,
                    brand: "boox"
                },
                {
                    id: 202,
                    name: "Boox Note Air 3C",
                    image: "assets/images/product/boox_go_7.png",
                    currentPrice: "15.990.000 đ",
                    oldPrice: "Giá NY: 17.990.000 đ",
                    discount: "-11%",
                    rating: 5,
                    brand: "boox"
                }
            ],
            'kindle': [
                {
                    id: 301,
                    name: "Kindle Paperwhite 5",
                    image: "assets/images/product/boox_go_7.png",
                    currentPrice: "3.290.000 đ",
                    oldPrice: "Giá NY: 3.990.000 đ",
                    discount: "-18%",
                    rating: 5,
                    brand: "kindle"
                },
                {
                    id: 302,
                    name: "Kindle Oasis",
                    image: "assets/images/product/boox_go_7.png",
                    currentPrice: "6.990.000 đ",
                    oldPrice: "Giá NY: 7.990.000 đ",
                    discount: "-13%",
                    rating: 5,
                    brand: "kindle"
                }
            ],
            'kobo': [
                {
                    id: 401,
                    name: "Kobo Libra 2",
                    image: "assets/images/product/boox_go_7.png",
                    currentPrice: "4.890.000 đ",
                    oldPrice: "Giá NY: 5.490.000 đ",
                    discount: "-11%",
                    rating: 5,
                    brand: "kobo"
                },
                {
                    id: 402,
                    name: "Kobo Clara HD",
                    image: "assets/images/product/boox_go_7.png",
                    currentPrice: "3.290.000 đ",
                    oldPrice: "Giá NY: 3.790.000 đ",
                    discount: "-13%",
                    rating: 5,
                    brand: "kobo"
                }
            ],
            'remarkable': [
                {
                    id: 501,
                    name: "reMarkable 2",
                    image: "assets/images/product/boox_go_7.png",
                    currentPrice: "8.990.000 đ",
                    oldPrice: "Giá NY: 9.990.000 đ",
                    discount: "-10%",
                    rating: 5,
                    brand: "remarkable"
                },
                {
                    id: 502,
                    name: "reMarkable Paper Pro",
                    image: "assets/images/product/boox_go_7.png",
                    currentPrice: "12.990.000 đ",
                    oldPrice: "Giá NY: 14.990.000 đ",
                    discount: "-13%",
                    rating: 5,
                    brand: "remarkable"
                }
            ]
        };
        
        return brandProducts[brand] || [];
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
