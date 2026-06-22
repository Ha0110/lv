<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!$this->supportsForeignKeyLookup()) {
            return;
        }

        $this->addForeignIfMissing('anh', 'fk_anh_bienThe', function (): void {
            Schema::table('anh', function (Blueprint $table) {
                $table->foreign('maBienThe', 'fk_anh_bienThe')
                    ->references('maBienThe')
                    ->on('bienthesanpham')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        });

        $this->addForeignIfMissing('bienthesanpham', 'fk_bienThe_sanPham', function (): void {
            Schema::table('bienthesanpham', function (Blueprint $table) {
                $table->foreign('maSanPham', 'fk_bienThe_sanPham')
                    ->references('maSanPham')
                    ->on('sanpham')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        });

        $this->addForeignIfMissing('chitietdonhang', 'fk_ctdh_bienThe', function (): void {
            Schema::table('chitietdonhang', function (Blueprint $table) {
                $table->foreign('maBienThe', 'fk_ctdh_bienThe')
                    ->references('maBienThe')
                    ->on('bienthesanpham')
                    ->onUpdate('cascade');
            });
        });

        $this->addForeignIfMissing('chitietdonhang', 'fk_ctdh_donHang', function (): void {
            Schema::table('chitietdonhang', function (Blueprint $table) {
                $table->foreign('maDonHang', 'fk_ctdh_donHang')
                    ->references('maDonHang')
                    ->on('donhang')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        });

        $this->addForeignIfMissing('chitietgiohang', 'fk_ctgh_bienThe', function (): void {
            Schema::table('chitietgiohang', function (Blueprint $table) {
                $table->foreign('maBienThe', 'fk_ctgh_bienThe')
                    ->references('maBienThe')
                    ->on('bienthesanpham')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        });

        $this->addForeignIfMissing('chitietgiohang', 'fk_ctgh_gioHang', function (): void {
            Schema::table('chitietgiohang', function (Blueprint $table) {
                $table->foreign('maGioHang', 'fk_ctgh_gioHang')
                    ->references('maGioHang')
                    ->on('giohang')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        });

        $this->addForeignIfMissing('chitietthuoctinh', 'chitietthuoctinh_ibfk_1', function (): void {
            Schema::table('chitietthuoctinh', function (Blueprint $table) {
                $table->foreign('maBienThe', 'chitietthuoctinh_ibfk_1')
                    ->references('maBienThe')
                    ->on('bienthesanpham');
            });
        });

        $this->addForeignIfMissing('chitietthuoctinh', 'chitietthuoctinh_ibfk_2', function (): void {
            Schema::table('chitietthuoctinh', function (Blueprint $table) {
                $table->foreign('maTT', 'chitietthuoctinh_ibfk_2')
                    ->references('maTT')
                    ->on('thuoctinh');
            });
        });

        $this->addForeignIfMissing('diachi', 'fk_diaChi_nguoiDung', function (): void {
            Schema::table('diachi', function (Blueprint $table) {
                $table->foreign('sdt', 'fk_diaChi_nguoiDung')
                    ->references('sdt')
                    ->on('nguoidung')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        });

        $this->addForeignIfMissing('donhang', 'fk_donHang_nguoiDung', function (): void {
            Schema::table('donhang', function (Blueprint $table) {
                $table->foreign('sdt', 'fk_donHang_nguoiDung')
                    ->references('sdt')
                    ->on('nguoidung')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        });

        $this->addForeignIfMissing('giohang', 'fk_gioHang_nguoiDung', function (): void {
            Schema::table('giohang', function (Blueprint $table) {
                $table->foreign('sdt', 'fk_gioHang_nguoiDung')
                    ->references('sdt')
                    ->on('nguoidung')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        });

        $this->addForeignIfMissing('khuyenmai', 'fk_km_quatang', function (): void {
            Schema::table('khuyenmai', function (Blueprint $table) {
                $table->foreign('maBienTheQuaTang', 'fk_km_quatang')
                    ->references('maBienThe')
                    ->on('bienthesanpham');
            });
        });

        $this->addForeignIfMissing('sanpham', 'fk_sanPham_danhMuc', function (): void {
            Schema::table('sanpham', function (Blueprint $table) {
                $table->foreign('maDanhMuc', 'fk_sanPham_danhMuc')
                    ->references('maDanhMuc')
                    ->on('danhmuc')
                    ->onUpdate('cascade');
            });
        });

        $this->addForeignIfMissing('sanpham', 'fk_sanPham_hangSanXuat', function (): void {
            Schema::table('sanpham', function (Blueprint $table) {
                $table->foreign('maHangSanXuat', 'fk_sanPham_hangSanXuat')
                    ->references('maHangSanXuat')
                    ->on('hangsanxuat')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        });

        $this->addForeignIfMissing('thanhtoan', 'fk_thanhToan_donHang', function (): void {
            Schema::table('thanhtoan', function (Blueprint $table) {
                $table->foreign('maDonHang', 'fk_thanhToan_donHang')
                    ->references('maDonHang')
                    ->on('donhang')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        });

        $this->addForeignIfMissing('thuoctinh', 'fk_tt_dm', function (): void {
            Schema::table('thuoctinh', function (Blueprint $table) {
                $table->foreign('maDanhMuc', 'fk_tt_dm')
                    ->references('maDanhMuc')
                    ->on('danhmuc');
            });
        });
    }

    public function down(): void
    {
        if (!$this->supportsForeignKeyLookup()) {
            return;
        }

        $this->dropForeignIfExists('thuoctinh', 'fk_tt_dm');
        $this->dropForeignIfExists('thanhtoan', 'fk_thanhToan_donHang');
        $this->dropForeignIfExists('sanpham', 'fk_sanPham_hangSanXuat');
        $this->dropForeignIfExists('sanpham', 'fk_sanPham_danhMuc');
        $this->dropForeignIfExists('khuyenmai', 'fk_km_quatang');
        $this->dropForeignIfExists('giohang', 'fk_gioHang_nguoiDung');
        $this->dropForeignIfExists('donhang', 'fk_donHang_nguoiDung');
        $this->dropForeignIfExists('diachi', 'fk_diaChi_nguoiDung');
        $this->dropForeignIfExists('chitietthuoctinh', 'chitietthuoctinh_ibfk_2');
        $this->dropForeignIfExists('chitietthuoctinh', 'chitietthuoctinh_ibfk_1');
        $this->dropForeignIfExists('chitietgiohang', 'fk_ctgh_gioHang');
        $this->dropForeignIfExists('chitietgiohang', 'fk_ctgh_bienThe');
        $this->dropForeignIfExists('chitietdonhang', 'fk_ctdh_donHang');
        $this->dropForeignIfExists('chitietdonhang', 'fk_ctdh_bienThe');
        $this->dropForeignIfExists('bienthesanpham', 'fk_bienThe_sanPham');
        $this->dropForeignIfExists('anh', 'fk_anh_bienThe');
    }

    private function addForeignIfMissing(string $table, string $constraint, callable $callback): void
    {
        if (Schema::hasTable($table) && !$this->foreignKeyExists($table, $constraint)) {
            $callback();
        }
    }

    private function dropForeignIfExists(string $table, string $constraint): void
    {
        if (Schema::hasTable($table) && $this->foreignKeyExists($table, $constraint)) {
            Schema::table($table, function (Blueprint $table) use ($constraint) {
                $table->dropForeign($constraint);
            });
        }
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $result = DB::selectOne(
            "SELECT COUNT(*) AS aggregate
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ?
                AND TABLE_NAME = ?
                AND CONSTRAINT_NAME = ?
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [DB::getDatabaseName(), $table, $constraint]
        );

        return (int) ($result->aggregate ?? 0) > 0;
    }

    private function supportsForeignKeyLookup(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
