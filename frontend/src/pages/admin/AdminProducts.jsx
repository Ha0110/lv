import { Fragment, useEffect, useMemo, useState } from "react";
import {
  createAdminProduct,
  deleteAdminProduct,
  fetchAdminProductMeta,
  fetchAdminProducts,
  uploadAdminProductImage,
  updateAdminProduct,
} from "../../services/api";
import { formatDate, formatMoney } from "./adminUtils";

// Mỗi sản phẩm phải có ít nhất một biến thể để lưu giá, tồn kho, ảnh và thuộc tính.
const createEmptyVariant = () => ({
  maBienThe: "",
  gia: "",
  soLuongTon: "",
  duongDanAnh: "",
  duongDanAnhUrl: "",
  thongSo: [],
});

const emptyProductForm = {
  tenSanPham: "",
  maDanhMuc: "",
  maHangSanXuat: "",
  moTa: "",
  bienThes: [createEmptyVariant()],
};

export default function AdminProducts({ canDeleteProducts }) {
  const [products, setProducts] = useState([]);
  const [productSummary, setProductSummary] = useState({
    total: 0,
    variants: 0,
    stock: 0,
    outOfStock: 0,
  });
  const [productMeta, setProductMeta] = useState({
    categories: [],
    manufacturers: [],
    attributes: [],
  });
  const [productsLoading, setProductsLoading] = useState(false);
  const [productError, setProductError] = useState("");
  const [productSearch, setProductSearch] = useState("");
  const [categoryFilter, setCategoryFilter] = useState("all");
  const [productModalOpen, setProductModalOpen] = useState(false);
  const [editingProduct, setEditingProduct] = useState(null);
  const [productForm, setProductForm] = useState(emptyProductForm);
  const [savingProduct, setSavingProduct] = useState(false);
  const [uploadingVariantIndex, setUploadingVariantIndex] = useState(null);

  // Danh sách sản phẩm đã bao gồm biến thể, ảnh và thống kê tồn kho.
  const loadProducts = async () => {
    setProductsLoading(true);
    setProductError("");

    try {
      const data = await fetchAdminProducts();
      setProducts(data.products || []);
      setProductSummary(data.summary || productSummary);
    } catch (err) {
      setProductError(err.message || "Không thể tải danh sách sản phẩm");
    } finally {
      setProductsLoading(false);
    }
  };

  // Metadata dùng cho form: danh mục, hãng và thuộc tính theo danh mục.
  const loadProductMeta = async () => {
    try {
      const data = await fetchAdminProductMeta();
      setProductMeta({
        categories: data.categories || [],
        manufacturers: data.manufacturers || [],
        attributes: data.attributes || [],
      });
    } catch (err) {
      setProductError(err.message || "Không thể tải dữ liệu sản phẩm");
    }
  };

  useEffect(() => {
    loadProducts();
    loadProductMeta();
  }, []);

  // Tìm kiếm/lọc cục bộ để bảng phản hồi ngay khi người dùng gõ.
  const filteredProducts = useMemo(() => {
    const keyword = productSearch.trim().toLowerCase();

    return products.filter((product) => {
      const matchesCategory =
        categoryFilter === "all" || product.maDanhMuc === categoryFilter;
      const source = [
        product.maSanPham,
        product.tenSanPham,
        product.tenDanhMuc,
        product.tenHang,
      ]
        .filter(Boolean)
        .join(" ")
        .toLowerCase();
      const matchesSearch = !keyword || source.includes(keyword);

      return matchesCategory && matchesSearch;
    });
  }, [categoryFilter, productSearch, products]);

  const productStats = [
    { label: "Sản phẩm", value: productSummary.total || products.length },
    { label: "Biến thể", value: productSummary.variants || 0 },
    { label: "Tồn kho", value: productSummary.stock || 0 },
    { label: "Hết hàng", value: productSummary.outOfStock || 0 },
  ];

  // Khi chọn danh mục, chỉ hiện các thuộc tính thuộc danh mục đó.
  const attributesForForm = useMemo(() => {
    if (!productForm.maDanhMuc) {
      return productMeta.attributes;
    }

    return productMeta.attributes.filter(
      (attribute) => attribute.maDanhMuc === productForm.maDanhMuc
    );
  }, [productForm.maDanhMuc, productMeta.attributes]);

  const updateProductForm = (field, value) => {
    setProductForm((current) => ({
      ...current,
      [field]: value,
    }));
  };

  const resetProductForm = () => {
    setEditingProduct(null);
    setProductForm({
      ...emptyProductForm,
      maDanhMuc: productMeta.categories[0]?.maDanhMuc || "",
      maHangSanXuat: productMeta.manufacturers[0]?.maHangSanXuat || "",
      bienThes: [createEmptyVariant()],
    });
  };

  const openCreateProduct = () => {
    resetProductForm();
    setProductError("");
    setProductModalOpen(true);
  };

  const openEditProduct = (product) => {
    setEditingProduct(product);
    setProductError("");
    // Chuyển dữ liệu API về đúng cấu trúc form nhiều biến thể.
    setProductForm({
      tenSanPham: product.tenSanPham || "",
      maDanhMuc: product.maDanhMuc || "",
      maHangSanXuat: product.maHangSanXuat || "",
      moTa: product.moTa || "",
      bienThes: product.bienThes?.length
        ? product.bienThes.map((variant) => ({
            maBienThe: variant.maBienThe || "",
            gia: variant.gia || "",
            soLuongTon: variant.soLuongTon || "",
            duongDanAnh: variant.duongDanAnh || "",
            duongDanAnhUrl: variant.duongDanAnhUrl || "",
            thongSo: variant.thongSo?.length
              ? variant.thongSo.map((spec) => ({
                  maTT: spec.maTT,
                  giaTri: spec.giaTri || "",
                }))
              : [],
          }))
        : [
            {
              ...createEmptyVariant(),
              gia: product.gia || "",
              soLuongTon: product.soLuongTon || "",
              duongDanAnh: product.duongDanAnh || "",
              duongDanAnhUrl: product.duongDanAnhUrl || "",
              thongSo: product.thongSo || [],
            },
          ],
    });
    setProductModalOpen(true);
  };

  const addVariant = () => {
    setProductForm((current) => ({
      ...current,
      bienThes: [...current.bienThes, createEmptyVariant()],
    }));
  };

  const updateVariantField = (variantIndex, field, value) => {
    setProductForm((current) => ({
      ...current,
      bienThes: current.bienThes.map((variant, index) =>
        index === variantIndex ? { ...variant, [field]: value } : variant
      ),
    }));
  };

  const handleVariantImageUpload = async (variantIndex, file) => {
    if (!file) return;

    setUploadingVariantIndex(variantIndex);
    setProductError("");

    try {
      // Backend trả path để lưu CSDL và url để preview ngay trên form.
      const data = await uploadAdminProductImage(file);

      setProductForm((current) => ({
        ...current,
        bienThes: current.bienThes.map((variant, index) =>
          index === variantIndex
            ? {
                ...variant,
                duongDanAnh: data.path,
                duongDanAnhUrl: data.url,
              }
            : variant
        ),
      }));
    } catch (err) {
      setProductError(err.message || "Không thể tải ảnh lên");
    } finally {
      setUploadingVariantIndex(null);
    }
  };

  const clearVariantImage = (variantIndex) => {
    setProductForm((current) => ({
      ...current,
      bienThes: current.bienThes.map((variant, index) =>
        index === variantIndex
          ? { ...variant, duongDanAnh: "", duongDanAnhUrl: "" }
          : variant
      ),
    }));
  };

  const removeVariant = (variantIndex) => {
    setProductForm((current) => ({
      ...current,
      bienThes:
        current.bienThes.length === 1
          ? current.bienThes
          : current.bienThes.filter((_, index) => index !== variantIndex),
    }));
  };

  const addSpecRow = (variantIndex) => {
    setProductForm((current) => ({
      ...current,
      bienThes: current.bienThes.map((variant, index) =>
        index === variantIndex
          ? {
              ...variant,
              thongSo: [...variant.thongSo, { maTT: "", giaTri: "" }],
            }
          : variant
      ),
    }));
  };

  const updateSpecRow = (variantIndex, specIndex, field, value) => {
    setProductForm((current) => ({
      ...current,
      bienThes: current.bienThes.map((variant, index) =>
        index === variantIndex
          ? {
              ...variant,
              thongSo: variant.thongSo.map((spec, currentSpecIndex) =>
                currentSpecIndex === specIndex ? { ...spec, [field]: value } : spec
              ),
            }
          : variant
      ),
    }));
  };

  const removeSpecRow = (variantIndex, specIndex) => {
    setProductForm((current) => ({
      ...current,
      bienThes: current.bienThes.map((variant, index) =>
        index === variantIndex
          ? {
              ...variant,
              thongSo: variant.thongSo.filter(
                (_, currentSpecIndex) => currentSpecIndex !== specIndex
              ),
            }
          : variant
      ),
    }));
  };

  // Payload chỉ gửi dữ liệu backend cần lưu, không gửi URL preview của ảnh.
  const productPayload = {
    tenSanPham: productForm.tenSanPham,
    maDanhMuc: productForm.maDanhMuc,
    maHangSanXuat: productForm.maHangSanXuat || null,
    moTa: productForm.moTa,
    bienThes: productForm.bienThes.map((variant) => ({
      maBienThe: variant.maBienThe || null,
      gia: Number(variant.gia || 0),
      soLuongTon: Number(variant.soLuongTon || 0),
      duongDanAnh: variant.duongDanAnh || null,
      thongSo: variant.thongSo.filter((spec) => spec.maTT && spec.giaTri),
    })),
  };

  const handleProductSubmit = async (event) => {
    event.preventDefault();
    setSavingProduct(true);
    setProductError("");

    try {
      if (editingProduct) {
        await updateAdminProduct(editingProduct.maSanPham, productPayload);
      } else {
        await createAdminProduct(productPayload);
      }

      await loadProducts();
      setProductModalOpen(false);
      resetProductForm();
    } catch (err) {
      setProductError(err.message || "Không thể lưu sản phẩm");
    } finally {
      setSavingProduct(false);
    }
  };

  const handleDeleteProduct = async (product) => {
    if (!window.confirm(`Xóa sản phẩm "${product.tenSanPham}"?`)) {
      return;
    }

    setProductError("");

    try {
      await deleteAdminProduct(product.maSanPham);
      await loadProducts();
    } catch (err) {
      setProductError(err.message || "Không thể xóa sản phẩm");
    }
  };

  return (
    <>
      <div className="admin-section-header">
        <div>
          <span className="admin-kicker">Kho hàng</span>
          <h2>Quản lý sản phẩm</h2>
        </div>

        <div className="admin-actions">
          <button className="admin-refresh" type="button" onClick={loadProducts}>
            Tải lại
          </button>
          <button
            className="admin-primary-action"
            type="button"
            onClick={openCreateProduct}
          >
            Thêm sản phẩm
          </button>
        </div>
      </div>

      <div className="admin-stats product-stats">
        {productStats.map((item) => (
          <div className="admin-stat" key={item.label}>
            <span>{item.label}</span>
            <strong>{item.value}</strong>
          </div>
        ))}
      </div>

      <div className="admin-toolbar">
        <div className="admin-search">
          <label htmlFor="product-search">Tìm kiếm</label>
          <input
            id="product-search"
            type="search"
            value={productSearch}
            onChange={(event) => setProductSearch(event.target.value)}
            placeholder="Tên sản phẩm, mã, danh mục hoặc hãng"
          />
        </div>

        <div className="admin-filter">
          <label htmlFor="category-filter">Danh mục</label>
          <select
            id="category-filter"
            value={categoryFilter}
            onChange={(event) => setCategoryFilter(event.target.value)}
          >
            <option value="all">Tất cả</option>
            {productMeta.categories.map((category) => (
              <option key={category.maDanhMuc} value={category.maDanhMuc}>
                {category.tenDanhMuc}
              </option>
            ))}
          </select>
        </div>
      </div>

      {productError && <div className="admin-alert">{productError}</div>}

      <div className="admin-table-wrap">
        {productsLoading ? (
          <div className="admin-empty">Đang tải danh sách sản phẩm...</div>
        ) : filteredProducts.length === 0 ? (
          <div className="admin-empty">Không có sản phẩm phù hợp</div>
        ) : (
          <table className="admin-table product-table">
            <thead>
              <tr>
                <th>Sản phẩm</th>
                <th>Danh mục</th>
                <th>Hãng</th>
                <th>Giá</th>
                <th>Tồn kho</th>
                <th>Biến thể</th>
                <th>Cập nhật</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              {filteredProducts.map((product) => (
                <Fragment key={product.maSanPham}>
                  <tr>
                    <td>
                      <div className="admin-user-cell product-cell">
                        <span>
                          {String(product.tenSanPham || "S")
                            .charAt(0)
                            .toUpperCase()}
                        </span>
                        <div>
                          <strong>{product.tenSanPham}</strong>
                          <small>{product.maSanPham}</small>
                        </div>
                      </div>
                    </td>
                    <td>{product.tenDanhMuc || "Chưa phân loại"}</td>
                    <td>{product.tenHang || "Chưa có"}</td>
                    <td>
                      {product.giaThapNhat === product.giaCaoNhat
                        ? formatMoney(product.giaThapNhat)
                        : `${formatMoney(product.giaThapNhat)} - ${formatMoney(
                            product.giaCaoNhat
                          )}`}
                    </td>
                    <td>{product.tongTonKho}</td>
                    <td>{product.soBienThe}</td>
                    <td>{formatDate(product.updatedAt)}</td>
                    <td>
                      <div className="row-actions">
                        <button type="button" onClick={() => openEditProduct(product)}>
                          Sửa
                        </button>
                        {canDeleteProducts && (
                          <button
                            className="danger"
                            type="button"
                            onClick={() => handleDeleteProduct(product)}
                          >
                            Xóa
                          </button>
                        )}
                      </div>
                    </td>
                  </tr>
                  <tr className="variant-detail-row">
                    <td colSpan="8">
                      <div className="variant-list">
                        {(product.bienThes || []).map((variant, index) => (
                          <div className="variant-card" key={variant.maBienThe || index}>
                            {variant.duongDanAnhUrl ? (
                              <img
                                className="variant-thumb"
                                src={variant.duongDanAnhUrl}
                                alt={`Ảnh biến thể ${index + 1}`}
                              />
                            ) : (
                              <div className="variant-thumb variant-thumb-empty">
                                Chưa có ảnh
                              </div>
                            )}
                            <div className="variant-card-header">
                              <strong>Biến thể {index + 1}</strong>
                              <span>{variant.maBienThe}</span>
                            </div>
                            <div className="variant-metrics">
                              <span>{formatMoney(variant.gia)}</span>
                              <span>Tồn: {variant.soLuongTon}</span>
                            </div>
                            {variant.thongSo?.length ? (
                              <div className="variant-specs">
                                {variant.thongSo.map((spec) => (
                                  <span key={`${variant.maBienThe}-${spec.maTT}`}>
                                    {spec.tenThuocTinh}: {spec.giaTri}
                                  </span>
                                ))}
                              </div>
                            ) : (
                              <p className="muted-text">Chưa có thuộc tính.</p>
                            )}
                          </div>
                        ))}
                      </div>
                    </td>
                  </tr>
                </Fragment>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {productModalOpen && (
        <div className="admin-modal-backdrop">
          <div className="admin-modal" role="dialog" aria-modal="true">
            <div className="admin-modal-header">
              <div>
                <span className="admin-kicker">Sản phẩm</span>
                <h3>{editingProduct ? "Cập nhật sản phẩm" : "Thêm sản phẩm"}</h3>
              </div>
              <button
                className="admin-icon-button"
                type="button"
                onClick={() => setProductModalOpen(false)}
                aria-label="Đóng"
              >
                ×
              </button>
            </div>

            <form className="product-form" onSubmit={handleProductSubmit}>
              <div className="form-grid">
                <div className="form-field span-2">
                  <label htmlFor="tenSanPham">Tên sản phẩm</label>
                  <input
                    id="tenSanPham"
                    value={productForm.tenSanPham}
                    onChange={(event) => updateProductForm("tenSanPham", event.target.value)}
                    required
                  />
                </div>

                <div className="form-field">
                  <label htmlFor="maDanhMuc">Danh mục</label>
                  <select
                    id="maDanhMuc"
                    value={productForm.maDanhMuc}
                    onChange={(event) => updateProductForm("maDanhMuc", event.target.value)}
                    required
                  >
                    <option value="">Chọn danh mục</option>
                    {productMeta.categories.map((category) => (
                      <option key={category.maDanhMuc} value={category.maDanhMuc}>
                        {category.tenDanhMuc}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="form-field">
                  <label htmlFor="maHangSanXuat">Hãng sản xuất</label>
                  <select
                    id="maHangSanXuat"
                    value={productForm.maHangSanXuat}
                    onChange={(event) =>
                      updateProductForm("maHangSanXuat", event.target.value)
                    }
                  >
                    <option value="">Chưa chọn</option>
                    {productMeta.manufacturers.map((manufacturer) => (
                      <option
                        key={manufacturer.maHangSanXuat}
                        value={manufacturer.maHangSanXuat}
                      >
                        {manufacturer.tenHang}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="form-field span-2">
                  <label htmlFor="moTa">Mô tả</label>
                  <textarea
                    id="moTa"
                    rows="4"
                    value={productForm.moTa}
                    onChange={(event) => updateProductForm("moTa", event.target.value)}
                  />
                </div>
              </div>

              <div className="variant-editor">
                <div className="spec-editor-header">
                  <strong>Danh sách biến thể</strong>
                  <button type="button" onClick={addVariant}>
                    Thêm biến thể
                  </button>
                </div>

                {productForm.bienThes.map((variant, variantIndex) => (
                  <div className="variant-form-card" key={`${variant.maBienThe}-${variantIndex}`}>
                    <div className="variant-form-header">
                      <div>
                        <strong>Biến thể {variantIndex + 1}</strong>
                        {variant.maBienThe && <small>{variant.maBienThe}</small>}
                      </div>
                      <button
                        className="danger"
                        type="button"
                        onClick={() => removeVariant(variantIndex)}
                        disabled={productForm.bienThes.length === 1}
                      >
                        Xóa biến thể
                      </button>
                    </div>

                    <div className="variant-form-grid">
                      <div className="form-field">
                        <label>Giá</label>
                        <input
                          type="number"
                          min="0"
                          value={variant.gia}
                          onChange={(event) =>
                            updateVariantField(variantIndex, "gia", event.target.value)
                          }
                          required
                        />
                      </div>

                      <div className="form-field">
                        <label>Tồn kho</label>
                        <input
                          type="number"
                          min="0"
                          value={variant.soLuongTon}
                          onChange={(event) =>
                            updateVariantField(
                              variantIndex,
                              "soLuongTon",
                              event.target.value
                            )
                          }
                          required
                        />
                      </div>

                      <div className="form-field variant-image-field">
                        <label>Ảnh biến thể</label>
                        <div className="variant-image-picker">
                          {variant.duongDanAnhUrl ? (
                            <img
                              src={variant.duongDanAnhUrl}
                              alt={`Ảnh biến thể ${variantIndex + 1}`}
                            />
                          ) : (
                            <div className="image-placeholder">Chưa có ảnh</div>
                          )}
                          <div className="variant-image-controls">
                            <input
                              type="file"
                              accept="image/*"
                              onChange={(event) => {
                                handleVariantImageUpload(
                                  variantIndex,
                                  event.target.files?.[0]
                                );
                                event.target.value = "";
                              }}
                            />
                            <small>
                              {uploadingVariantIndex === variantIndex
                                ? "Đang tải ảnh..."
                                : variant.duongDanAnh || "Chọn ảnh từ máy"}
                            </small>
                            {variant.duongDanAnh && (
                              <button
                                type="button"
                                onClick={() => clearVariantImage(variantIndex)}
                              >
                                Gỡ ảnh
                              </button>
                            )}
                          </div>
                        </div>
                      </div>
                    </div>

                    <div className="variant-spec-editor">
                      <div className="spec-editor-header compact">
                        <strong>Thuộc tính biến thể</strong>
                        <button type="button" onClick={() => addSpecRow(variantIndex)}>
                          Thêm thuộc tính
                        </button>
                      </div>

                      {variant.thongSo.length === 0 ? (
                        <p className="muted-text">Chưa có thuộc tính.</p>
                      ) : (
                        variant.thongSo.map((spec, specIndex) => (
                          <div
                            className="spec-row"
                            key={`${variant.maBienThe}-${spec.maTT}-${specIndex}`}
                          >
                            <select
                              value={spec.maTT}
                              onChange={(event) =>
                                updateSpecRow(
                                  variantIndex,
                                  specIndex,
                                  "maTT",
                                  event.target.value
                                )
                              }
                            >
                              <option value="">Chọn thuộc tính</option>
                              {attributesForForm.map((attribute) => (
                                <option key={attribute.maTT} value={attribute.maTT}>
                                  {attribute.tenThuocTinh}
                                </option>
                              ))}
                            </select>
                            <input
                              value={spec.giaTri}
                              onChange={(event) =>
                                updateSpecRow(
                                  variantIndex,
                                  specIndex,
                                  "giaTri",
                                  event.target.value
                                )
                              }
                              placeholder="Giá trị"
                            />
                            <button
                              className="danger"
                              type="button"
                              onClick={() => removeSpecRow(variantIndex, specIndex)}
                            >
                              Xóa
                            </button>
                          </div>
                        ))
                      )}
                    </div>
                  </div>
                ))}
              </div>

              <div className="admin-modal-actions">
                <button
                  className="admin-refresh"
                  type="button"
                  onClick={() => setProductModalOpen(false)}
                >
                  Hủy
                </button>
                <button
                  className="admin-primary-action"
                  type="submit"
                  disabled={savingProduct}
                >
                  {savingProduct ? "Đang lưu..." : "Lưu sản phẩm"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </>
  );
}
