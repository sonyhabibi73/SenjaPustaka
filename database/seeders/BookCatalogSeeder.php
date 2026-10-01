<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\Database\Seeder;

/**
 * Katalog nyata SenjaPustaka: 13 kategori, 6 penulis, 4 penerbit, 14 buku.
 *
 * Sumber data:
 *  - 8 deskripsi + tahun + halaman  : database/database.sqlite.backup-20260812-011300
 *  - 1 deskripsi (Toumei)           : storage/logs/laravel.log
 *  - 5 deskripsi                    : disusun dari blurb/halaman buku itu sendiri
 *  - file & sampul                  : storage/app/public/{books,covers} (pasangan diverifikasi
 *                                    terhadap backup untuk 8 buku, sisanya selisih mtime)
 *
 * Idempoten: aman dijalankan berulang (updateOrCreate + sync).
 */
class BookCatalogSeeder extends Seeder
{
    /** @var array<int, array{0: string, 1: string, 2: string, 3: string}> */
    public const CATEGORIES = [
        ['Fiksi', 'fiksi', 'book-open', 'Cerita rekaan yang menghanyutkan.'],
        ['Fantasi', 'fantasi', 'sparkles', 'Dunia magis penuh petualangan.'],
        ['Romantis', 'romantis', 'heart', 'Kisah cinta yang menghangatkan hati.'],
        ['Misteri', 'misteri', 'search', 'Teka-teki yang menantang nalar.'],
        ['Sejarah', 'sejarah', 'landmark', 'Jejak masa lalu untuk masa depan.'],
        ['Teknologi', 'teknologi', 'cpu', 'Dunia digital dan inovasi.'],
        ['Sains', 'sains', 'atom', 'Penemuan yang mengubah cara pandang.'],
        ['Bisnis', 'bisnis', 'trending-up', 'Strategi dan semangat wirausaha.'],
        ['Komik', 'komik', 'palette', 'Visual yang bercerita.'],
        ['Self-Help', 'self-help', 'sprout', 'Tumbuh jadi versi terbaik dirimu.'],
        ['Puisi', 'puisi', 'feather', 'Kata-kata yang menyentuh jiwa.'],
        ['Biografi', 'biografi', 'user', 'Kisah nyata para inspirator.'],
        ['Novel', 'novel', 'book-open', 'Narasi panjang dengan alur dan tokoh yang berkembang.'],
    ];

    /** @var array<int, array{0: string, 1: string, 2: string}> nama, slug, bio */
    public const AUTHORS = [
        ['Nurwina Sari', 'nurwina-sari', 'Penulis Indonesia; novel 3726 MDPL diterbitkan pada 2024.'],
        ['Tere Liye', 'tere-liye', 'Nama pena Darwis (lahir 21 Mei 1979), penulis Indonesia yang memulai debut kepenulisan lewat Hafalan Sholat Delisa pada 2005.'],
        ['Leila S. Chudori', 'leila-s-chudori', 'Penulis Indonesia; novel Laut Bercerita diterbitkan Gramedia Pustaka Utama pada 2017.'],
        ['Brian Khrisna', 'brian-khrisna', 'Penulis Indonesia; karyanya antara lain Kudasai (2019), Sisi Tergelap Surga (2023), dan Bandung Menjelang Pagi (2024).'],
        ['Eka Kurniawan', 'eka-kurniawan', 'Lahir di Tasikmalaya pada 1975, lulusan Fakultas Filsafat Universitas Gadjah Mada (1999). Cantik Itu Luka adalah karya pertamanya (2002).'],
        ['Shima Nanigashi', 'shima-nanigashi', 'Mangaka asal Jepang; penulis Toumei na Yoru ni Kakeru Kimi to, Me ni Mienai Koi wo Shita (2024) dengan ilustrasi oleh hat.'],
    ];

    /** @var array<int, array{0: string, 1: string}> nama, slug */
    public const PUBLISHERS = [
        ['Gramedia Pustaka', 'gramedia-pustaka'],
        ['mediakita', 'mediakita'],
        ['bukune', 'bukune'],
        ['Republika', 'republika'],
    ];

    /** @var array<int, array<string, mixed>> */
    public const BOOKS = [
        [
            'title' => '3726 MDPL',
            'slug' => '3726-mdpl',
            'author' => 'nurwina-sari',
            'publisher' => 'gramedia-pustaka',
            'categories' => ['fiksi', 'romantis', 'novel'],
            'pages' => 153,
            'year' => 2024,
            'views' => 12,
            'is_featured' => true,
            'cover_color' => '#B4491A',
            'file_path' => 'books/HjAteOocFlblLbZp2OzzsD89g3bXQpc6d3nCIBU2.pdf',
            'cover_image' => 'covers/fISyPDKTysw7FG4TJcFBrqPGmN0ctU2T97qIFcIY.jpg',
            'description' => "Novel 3726 MDPL karya Nurwina Sari mengisahkan perjalanan cinta Rangga Raja, mahasiswa Fakultas Kehutanan yang telah diam-diam mengagumi adik tingkatnya, Andini Hangura, selama empat tahun. Rangga menunjukkan perhatiannya melalui pesan-pesan singkat dan ucapan ulang tahun, namun Andini yang masih terbelenggu luka masa lalu dan hubungan lamanya dengan sosok bernama Bintang, ragu untuk membalas perasaan tersebut. \n\nTitik balik terjadi ketika Rangga mengirimkan foto dari puncak Gunung Rinjani (setinggi 3.726 mdpl), impian terbesar Andini. Momen ini membuka komunikasi lebih intens di antara mereka, mengubah kekaguman sepihak menjadi kedekatan emosional. Namun, hubungan yang mulai terjalin diuji oleh bayang-bayang masa lalu Andini, keragu-raguan hatinya, serta restu orang tua yang tidak mendukung hubungan mereka. \n\nDi balik kisah romansa kampus, novel ini juga menonjolkan kehidupan mahasiswa kehutanan, keindahan alam pendakian, serta tema mendalam tentang ketulusan, kesabaran, dan ikhlas melepaskan. Judul buku ini diambil dari ketinggian Gunung Rinjani, yang menjadi simbol perjalanan fisik sekaligus emosional para tokohnya.",
        ],
        [
            'title' => 'Hujan',
            'slug' => 'hujan',
            'author' => 'tere-liye',
            'publisher' => 'gramedia-pustaka',
            'categories' => ['fiksi', 'novel'],
            'pages' => 315,
            'year' => 2016,
            'views' => 15,
            'is_featured' => false,
            'cover_color' => '#8A4B2A',
            'file_path' => 'books/hH0EJ3RSoI9zokR8EXLhGnxch4VR0gVyOKOTWIbC.pdf',
            'cover_image' => 'covers/nQyhD9zh8tiGs9Tb5qxI5rmVSLpVrm1bjKmNy8rJ.jpg',
            'description' => "Novel Hujan berlatar waktu masa depan (tahun 2042–2050) dengan sentuhan science fiction, mengisahkan tentang Lail, seorang gadis yang kehilangan kedua orang tuanya akibat bencana gunung meletus dan gempa dahsyat pada hari pertama sekolahnya. Di tengah kehancuran itu, ia diselamatkan oleh Esok, seorang pemuda yang juga menjadi yatim piatu karena bencana yang sama. \n\nMereka bertemu dan saling mengandalkan selama lebih dari satu tahun di tempat pengungsian, membangun ikatan persahabatan yang kemudian berkembang menjadi cinta. Namun, mereka terpaksa berpisah ketika pengungsian ditutup; Lail masuk ke panti sosial dan menjadi relawan kemanusiaan, sementara Esok diadopsi oleh keluarga Wali Kota dan melanjutkan pendidikan hingga menjadi ilmuwan jenius yang terlibat dalam proyek penyelamatan umat manusia ke luar angkasa. \n\nCerita dikemas dalam kilas balik saat Lail dewasa memutuskan untuk menghapus memorinya tentang Esok dan masa lalu yang menyakitkan menggunakan teknologi canggih, dengan didampingi fasilitator bernama Elijah. Novel ini mengeksplorasi tema cinta, kehilangan, trauma, teknologi masa depan, serta dilema manusia antara mengingat atau melupakan kenangan pahit.",
        ],
        [
            'title' => 'Laut Bercerita',
            'slug' => 'laut-bercerita',
            'author' => 'leila-s-chudori',
            'publisher' => 'gramedia-pustaka',
            'categories' => ['romantis', 'misteri', 'sejarah', 'novel'],
            'pages' => 394,
            'year' => 2017,
            'views' => 1,
            'is_featured' => true,
            'cover_color' => '#C2762F',
            'file_path' => 'books/xCJlhT00cKH8mPfxShPgqF0klgMpUnpeiJGARjhL.pdf',
            'cover_image' => 'covers/XnmlezwzmaoUtWEUibysotSXMoTUXH9bDh7CUpUs.webp',
            'description' => "Laut Bercerita menceritakan kehidupan Biru Laut, seorang mahasiswa yang aktif dalam gerakan mahasiswa pada masa Orde Baru. Bersama teman-temannya, Laut memperjuangkan kebebasan dan menentang ketidakadilan. Karena aktivitasnya, Laut dan beberapa kawannya ditangkap dan mengalami penyiksaan serta interogasi.\n\nCerita kemudian berlanjut dari sudut pandang Asmara Jati, adik Laut. Asmara dan keluarganya harus menghadapi kehilangan setelah Laut dinyatakan hilang. Mereka terus mencari keberadaan Laut dan aktivis lain yang juga menghilang. Novel ini menggambarkan perjuangan, persahabatan, keluarga, cinta, serta penderitaan para korban penghilangan paksa menjelang Reformasi 1998.",
        ],
        [
            'title' => 'Cantik Itu Luka',
            'slug' => 'cantik-itu-luka',
            'author' => 'eka-kurniawan',
            'publisher' => 'gramedia-pustaka',
            'categories' => ['fiksi', 'sejarah', 'novel'],
            'pages' => 490,
            'year' => 2002,
            'views' => 7,
            'is_featured' => true,
            'cover_color' => '#7A2E12',
            'file_path' => 'books/At26YQPWuJQzaEjgwKClJNuPyApCpObQxMiFyLRL.pdf',
            'cover_image' => 'covers/RhGeF1MQWhgdLydsYekk36tk2N75YSVJAJAtUFg7.webp',
            'description' => 'Karya pertama Eka Kurniawan, pertama terbit pada 2002 dan diterbitkan PT Gramedia Pustaka Utama. Novel ini membuka dengan gagasan bahwa kecantikan adalah luka, lalu menelusuri kehidupan Dewi Ayu — perempuan tercantik di kotanya — beserta keturunannya, dari masa pendudukan Jepang dan perjuangan kemerdekaan hingga pergolakan 1965 dan kerusuhan akhir 1990-an. Sejarah besar Indonesia berjalan berdampingan dengan kisah cinta, keluarga, kekerasan, dan sihir yang dituturkan lewat magis realisme. Setebal 490 halaman, novel ini telah diterbitkan dalam bahasa Jepang dengan judul Bi wa Kizu.',
        ],
        [
            'title' => 'Tentang Kamu',
            'slug' => 'tentang-kamu',
            'author' => 'tere-liye',
            'publisher' => 'republika',
            'categories' => ['fiksi', 'romantis', 'misteri', 'novel'],
            'pages' => 648,
            'year' => 2016,
            'views' => 5,
            'is_featured' => false,
            'cover_color' => '#A3541F',
            'file_path' => 'books/CzVr2YmtLHDKv8B7Edji5F4QcwAMJZKcKZjWG3P5.pdf',
            'cover_image' => 'covers/cu2ntisL6RhCotDi07HCHKHc0QjLWSoRsywdRK4P.webp',
            'description' => 'Novel Tere Liye terbitan Republika pada 2016 yang memenangi penghargaan fiksi pada Islamic Book Fair 2017. Ceritanya bergerak di antara London dan tanah air, mengikuti Zaman — pemuda berusia 30 tahun yang baru turun dari pesawat dan disambut sahabatnya, Rajendra Khan, seorang cendekiawan India yang menjadi tamu kehormatan dalam perayaan kenegaraan dengan jamuan makan siang di Buckingham Palace tahun 1977. Kota, perjalanan, pekerjaan, dan pertemuan dengan para sahabat menjadi latarnya, sementara nadanya dirangkum oleh epigraf pada sampul belakang edisi ini: “Cinta memang tidak perlu ditemukan, cintailah yang akan menemukan kita.”',
        ],
        [
            'title' => 'Rindu',
            'slug' => 'rindu',
            'author' => 'tere-liye',
            'publisher' => null,
            'categories' => ['fiksi', 'romantis', 'novel'],
            'pages' => 669,
            'year' => 2014,
            'views' => 6,
            'is_featured' => false,
            'cover_color' => '#96522A',
            'file_path' => 'books/K50TKwXAwcM9a64hb4uXWE1bDiuiKbmEemJ7qUoc.pdf',
            'cover_image' => 'covers/FypkfYw51cmfsw67QeIbUCeDnLImASV8K1NLp3Bu.webp',
            'description' => 'Novel Tere Liye yang terbit pada 2014 dan memenangi Islamic Book Award 2016 untuk kategori Buku Islami Terbaik Fiksi Dewasa. Ceritanya dibuka di atas kapal Panjunan — kapal besi sepanjang 16 meter dan setinggi 13 meter, dibangun di Eropa pada 1923 lalu dioperasikan tahun 1925 oleh sebuah perusahaan pelayaran Hindia Belanda — yang bersandar di pelabuhan Makassar, tidak jauh dari Fort Rotterdam. Dari geladak dan lorong kapal itulah kisah para penumpangnya dituturkan, pada masa ketika tanah air masih berada dalam genggaman kolonial. Setebal 669 halaman, novel ini ditutup surat penulis bertanggal Bandung, 16 Agustus 2014.',
        ],
        [
            'title' => 'Janji',
            'slug' => 'janji',
            'author' => 'tere-liye',
            'publisher' => null,
            'categories' => ['misteri', 'novel'],
            'pages' => 927,
            'year' => 2021,
            'views' => 1,
            'is_featured' => false,
            'cover_color' => '#D08A3E',
            'file_path' => 'books/qw2CzLHukRD4r79mDG7eskUrCaiBEiTnmQ6skdZs.pdf',
            'cover_image' => 'covers/55kkqLhSxCKpjFMFiykXKtvj0itC2CU0HSXoK10T.webp',
            'description' => "Kisah ini berpusat pada tiga sekawan santri nakal bernama Hasan, Baso, dan Kahar di sebuah sekolah agama. Akibat kenakalan ekstrem mereka—termasuk insiden menggarami teh tamu penting (calon Presiden) dan Buya—mereka tidak dihukum secara konvensional, melainkan diberi tugas khusus: mencari sosok Bahar Safar, seorang alumni yang diusir 40 tahun lalu karena kenakalan yang jauh lebih parah, hingga menyebabkan kematian seorang santri dan kebakaran pesantren. \n\nPerjalanan pencarian ini menjadi napak tilas kehidupan Bahar, mengungkap transformasinya dari anak bermasalah menjadi pribadi yang mulia. Terungkap bahwa Buya pendiri pesantren sering bermimpi melihat Bahar mendapat keistimewaan di akhirat, memicu penyesalan atas keputusan mengusirnya. Melalui petualangan melintasi berbagai tempat (dari penjara hingga pertambangan liar), tiga sekawan ini belajar makna kehidupan, pertobatan, dan hakikat menepati janji kepada Tuhan dan sesama, meski harus dibayar dengan rasa sakit dan air mata. Novel ini menekankan bahwa kemuliaan manusia tidak diukur dari masa lalu atau materi, melainkan dari keteguhan menunaikan janji.",
        ],
        [
            'title' => 'Kudasai',
            'slug' => 'kudasai',
            'author' => 'brian-khrisna',
            'publisher' => 'gramedia-pustaka',
            'categories' => ['fiksi', 'romantis', 'novel'],
            'pages' => 454,
            'year' => 2019,
            'views' => 1,
            'is_featured' => false,
            'cover_color' => '#8C3A1B',
            'file_path' => 'books/26VVjc8XTpSaQ354bF1CIBm7mXqWhUXS3CRw98k5.pdf',
            'cover_image' => 'covers/ZNmGQ02tLczSJWpY0zvC0HTq3QCUpDlw0PpgX0hh.webp',
            'description' => "Kudasai menceritakan Chaka, seorang pria yang terpaksa menikahi Twindy, perempuan yang sukses memimpin sebuah firma arsitek. Dalam rumah tangga mereka, Twindy menjadi tulang punggung keluarga, sedangkan Chaka lebih banyak mengurus rumah dan mengelola kafe milik mereka.\n\nSelama dua tahun, kehidupan pernikahan mereka berjalan dengan berbagai kejadian lucu dan konflik. Namun, masalah mulai muncul ketika Chaka tidak sengaja bertemu kembali dengan mantan kekasihnya yang masih menyimpan kisah yang belum selesai. Di tengah kebingungan antara masa lalu dan rumah tangganya, Chaka harus menentukan pilihan, terlebih setelah mengetahui bahwa Twindy sedang mengandung anaknya.",
        ],
        [
            'title' => 'Bandung Menjelang Pagi',
            'slug' => 'bandung-menjelang-pagi',
            'author' => 'brian-khrisna',
            'publisher' => 'gramedia-pustaka',
            'categories' => ['fiksi', 'romantis', 'novel'],
            'pages' => 308,
            'year' => 2024,
            'views' => 3,
            'is_featured' => false,
            'cover_color' => '#C25A33',
            'file_path' => 'books/dsIvdJyLJkOKUiaspu4bXBIRG0wZYouTbAHamEsY.pdf',
            'cover_image' => 'covers/Y3u3EfZQzfr43jFsSpsrfH8YeA8xBoDIo3eololO.webp',
            'description' => "Bandung Menjelang Pagi bercerita tentang Dipha, seorang pemuda serabutan yang melakukan berbagai pekerjaan untuk bertahan hidup di kerasnya kehidupan Kota Bandung. Ia bisa berjualan bacang di Asia Afrika, bekerja di kafe Braga, hingga menjadi buruh angkut.\n\nSuatu hari, Dipha bertemu dengan Vinda, seorang gadis misterius yang sangat mencintai Bandung. Takdir kemudian mempertemukan mereka kembali ketika Vinda menyewa kontrakan yang tepat berada di seberang tempat tinggal Dipha. Dari sana, keduanya mulai semakin dekat dan menyusuri berbagai sudut Bandung, seperti Asia Afrika, Braga, Dago, hingga Jalan ABC.\n\nDi balik kisah keduanya, novel ini juga menggambarkan sisi lain Bandung ketika malam menjelang pagi—kehidupan jalanan, masyarakat kelas bawah, dan berbagai masalah yang jarang terlihat oleh wisatawan. Kisah Dipha dan Vinda kemudian berkembang menjadi romansa yang manis sekaligus penuh luka dan patah hati.",
        ],
        [
            'title' => 'Sisi Tergelap Surga',
            'slug' => 'sisi-tergelap-surga',
            'author' => 'brian-khrisna',
            'publisher' => 'gramedia-pustaka',
            'categories' => ['fiksi', 'novel'],
            'pages' => 308,
            'year' => 2023,
            'views' => 2,
            'is_featured' => true,
            'cover_color' => '#6E2A10',
            'file_path' => 'books/y7K7R0fpm5RVp8jlVFW86KaiX6Bx6ZfK0n7gzarf.pdf',
            'cover_image' => 'covers/nyca7ENeA3OE3dDkhdGie6bPUrH4vz8TObiV8l4T.webp',
            'description' => "Sisi Tergelap Surga menceritakan kehidupan orang-orang yang tinggal di balik gemerlap Kota Jakarta. Jakarta menjadi tempat bagi banyak orang untuk mengejar harapan, tetapi bagi sebagian lainnya, kota tersebut justru menjadi tempat mereka berjuang keras untuk bertahan hidup.\n\nCerita memperlihatkan kehidupan berbagai kelompok masyarakat yang sering dipandang sebelah mata, seperti pemulung, pengamen, pekerja seks, pencuri, badut jalanan, manusia silver, hingga orang-orang yang hidup di sekitar terminal. Masing-masing memiliki masalah dan alasan sendiri untuk terus bertahan.\n\nMelalui kisah mereka, Brian Khrisna menunjukkan sisi Jakarta yang jarang terlihat—tentang kemiskinan, perjuangan, ketidakadilan, harapan, dan kerasnya kehidupan masyarakat kelas bawah.",
        ],
        [
            'title' => 'Seporsi Mie Ayam Sebelum Mati',
            'slug' => 'seporsi-mie-ayam-sebelum-mati',
            'author' => 'brian-khrisna',
            'publisher' => 'gramedia-pustaka',
            'categories' => ['fiksi', 'self-help', 'novel'],
            'pages' => 217,
            'year' => 2025,
            'views' => 1,
            'is_featured' => true,
            'cover_color' => '#A85A22',
            'file_path' => 'books/qmkDcRbQU9cXssER5FUxYSEDFzxRuqpksUK8iAyf.pdf',
            'cover_image' => 'covers/D0ABrcAZtplVzVfGaFyQ3f0aLIp5bROashluSfkz.webp',
            'description' => "Novel ini menceritakan Ale, seorang pria berusia 37 tahun yang sejak kecil sering mengalami perundungan dan merasa tidak diterima oleh lingkungan maupun keluarganya. Kondisi tersebut membuat Ale mengalami depresi dan merasa hidupnya tidak lagi memiliki arti.\n\nAle kemudian memutuskan untuk mengakhiri hidupnya. Namun, sebelum melakukan hal tersebut, ia ingin menikmati seporsi mie ayam untuk terakhir kalinya. Dalam perjalanan untuk mendapatkan mie ayam itu, Ale justru bertemu dengan berbagai orang dan mengalami kejadian yang perlahan membuatnya melihat kehidupan dari sudut pandang berbeda.\n\nKisah Ale menjadi perjalanan tentang kesepian, penerimaan diri, perjuangan menghadapi kehidupan, dan menemukan kembali alasan untuk bertahan hidup.",
        ],
        [
            'title' => 'Parable',
            'slug' => 'parable',
            'author' => 'brian-khrisna',
            'publisher' => 'mediakita',
            'categories' => ['fiksi', 'misteri', 'novel'],
            'pages' => 692,
            'year' => 2021,
            'views' => 4,
            'is_featured' => false,
            'cover_color' => '#B4491A',
            'file_path' => 'books/I9vbufSasi8PkLkQQS8720ZB5ukEKxpNfGd5a92V.pdf',
            'cover_image' => 'covers/RCDEqAGkS41HbueTx16TMEn2z8dwyXovtoHHKwkr.webp',
            'description' => 'Novel Brian Khrisna terbitan mediakita (cetakan pertama, 2021). Berpusat pada Sadewa Sagara, anak SMA dari keluarga miskin yang selalu berada di urutan terakhir kelas, tidak punya keahlian apa pun selain bernapas, dan terus-menerus tersisih: menembak sepuluh kali ditolak sebelas. Berbeda dari kisah remaja klise tentang tokoh tampan, kaya, dan populer, Parable justru mengisahkan seseorang yang tak pernah menjadi pemeran utama di kehidupannya sendiri. Setebal 692 halaman.',
        ],
        [
            'title' => 'Malioboro at Midnight',
            'slug' => 'malioboro-at-midnight',
            'author' => 'brian-khrisna',
            'publisher' => 'bukune',
            'categories' => ['fiksi', 'misteri', 'novel'],
            'pages' => 430,
            'year' => null,
            'views' => 3,
            'is_featured' => false,
            'cover_color' => '#7A2E12',
            'file_path' => 'books/hGszNBQhSQtAoMAGFZ3ATH1yGV0LL95OcTL2GyFP.pdf',
            'cover_image' => 'covers/txVGQ1EUwzPgnFTbLi0lAdXuc88Zo8CJ9v4G7Cbs.webp',
            'description' => 'Novel Brian Khrisna terbitan bukune, Yogyakarta. Tengah malam adalah waktu terbaik untuk beristirahat bagi kebanyakan orang — tetapi bukan bagi Serana Nighita. Popularitas Jan Ichard, sang penyanyi terkenal, membawa lelaki itu ke Jakarta dan meninggalkan Sera sendirian di Jogja, dan malam selalu menakutkan baginya. Kehidupannya berubah ketika Malioboro Hartigan tidak sengaja mendobrak pintu kamarnya pada suatu malam lalu menawarkan pertemanan agar Sera tidak sendiri. Sementara itu hubungan Sera dengan Jan Ichard yang jauh di Jakarta kian rumit. Setebal 430 halaman.',
        ],
        [
            'title' => 'Toumei na Yoru ni Kakeru Kimi to, Me ni Mienai Koi wo Shita.',
            'slug' => 'toumei-na-yoru-ni-kakeru-kimi-to-me-ni-mienai-koi-wo-shita',
            'author' => 'shima-nanigashi',
            'publisher' => null,
            'categories' => ['komik', 'romantis'],
            'pages' => 99,
            'year' => 2024,
            'views' => 8,
            'is_featured' => false,
            'cover_color' => '#E0854A',
            'file_path' => 'books/toumei-na-yoru-ni-kakeru-kimi-to-me-ni-mienai-koi-wo-shita.cbz',
            'cover_image' => 'covers/Vag5qD6AfCEQRRBfp5VR5phrjBU1Kxm1NZ1WnpI6.webp',
            'description' => 'Pada malam April di Tokyo, masih terlalu dini untuk kembang api, mahasiswa Kakeru Mano bertemu dengan seorang wanita bernama Koharu Fuyutsuki. Dia adalah seorang gadis cantik yang menonjol dari kerumunan, banyak tertawa, dan memancarkan kehangatan yang sangat kontras dengan introversi Kakeru sendiri. Tapi ada sesuatu yang Kakeru tidak tahu pada awalnya—dia tidak bisa melihat. Tidak seperti Kakeru, Koharu belum menyerah pada apa pun, bahkan dengan kebutaannya. Dia kuliah setiap hari di universitas, menunjukkan minat pada klub, berteman, dan bermimpi meluncurkan kembang api suatu hari nanti. Kakeru bertanya-tanya mengapa, ketika dia tidak bisa melihat mereka... tapi mungkin itu tidak masalah. Dan mungkin dia juga bisa mulai bergerak maju, untuk gadis yang selalu berada di sisinya.',
        ],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as [$name, $slug, $emoji, $description]) {
            Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'emoji' => $emoji, 'description' => $description]
            );
        }

        foreach (self::AUTHORS as [$name, $slug, $bio]) {
            Author::updateOrCreate(['slug' => $slug], ['name' => $name, 'bio' => $bio]);
        }

        foreach (self::PUBLISHERS as [$name, $slug]) {
            Publisher::updateOrCreate(['slug' => $slug], ['name' => $name]);
        }

        $categoryIds = Category::pluck('id', 'slug');
        $authorIds = Author::pluck('id', 'slug');
        $publisherIds = Publisher::pluck('id', 'slug');

        foreach (self::BOOKS as $data) {
            $authorId = $authorIds[$data['author']] ?? null;

            if (! $authorId) {
                $this->command?->warn('Penulis tidak ditemukan: '.$data['author']);
            }

            $book = Book::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'author_id' => $authorId,
                    'publisher_id' => $data['publisher'] ? ($publisherIds[$data['publisher']] ?? null) : null,
                    'description' => $data['description'],
                    // Konten tidak disimpan di database: pembaca memakai file PDF/CBZ.
                    'content' => null,
                    'cover_color' => $data['cover_color'],
                    'cover_image' => $data['cover_image'],
                    'file_path' => $data['file_path'],
                    'pages' => $data['pages'],
                    'year' => $data['year'],
                    'language' => 'id',
                    'views' => $data['views'],
                    'rating_avg' => 0,
                    'rating_count' => 0,
                    'is_featured' => $data['is_featured'],
                    'is_published' => true,
                ]
            );

            $book->categories()->sync(
                collect($data['categories'])
                    ->map(fn (string $slug) => $categoryIds[$slug])
                    ->filter()
                    ->values()
                    ->all()
            );
        }

        $this->command?->info(count(self::BOOKS).' buku nyata tersinkron.');
    }
}
