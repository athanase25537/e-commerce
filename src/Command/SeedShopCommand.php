<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\SubCategory;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-shop',
    description: 'Clear existing shop data and seed sample categories and products.'
)]
class SeedShopCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Skip confirmation and wipe existing data.');
    }

    private function applyImage(Product $product, string $source): void
    {
        if ($source === '') {
            return;
        }

        if (filter_var($source, FILTER_VALIDATE_URL)) {
            $data = @file_get_contents($source);
            if ($data !== false) {
                $mimeType = 'application/octet-stream';
                if (class_exists(\finfo::class)) {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $detected = $finfo->buffer($data);
                    if (is_string($detected) && $detected !== '') {
                        $mimeType = $detected;
                    }
                }
                $product->setImage($data);
                $product->setImageMimeType($mimeType);
                return;
            }
        }

        $product->setImage($source);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('force')) {
            if (!$io->confirm('This will delete existing catalog/order data. Continue?', false)) {
                $io->warning('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $isSqlite = $platform instanceof SqlitePlatform;
        if ($isSqlite) {
            $connection->executeStatement('PRAGMA foreign_keys = OFF');
        }

        $tables = [
            'add_product_history',
            'order_item',
            'review',
            'product_sub_category',
            'product',
            'sub_category',
            'category',
            'customer_order',
            'coupon',
        ];

        foreach ($tables as $table) {
            $connection->executeStatement('DELETE FROM ' . $table);
        }

        if ($isSqlite) {
            foreach ($tables as $table) {
                $connection->executeStatement('DELETE FROM sqlite_sequence WHERE name = ?', [$table]);
            }
            $connection->executeStatement('PRAGMA foreign_keys = ON');
        }

        $categoryMap = [];
        $subCategoryMap = [];
        $categories = [
            'Chaussures' => ['Sneakers', 'Derbies', 'Bottes', 'Sandales'],
            'Vetements' => ['T-shirts', 'Chemises', 'Vestes', 'Pantalons'],
            'Lunettes' => ['Solaires', 'Optiques'],
            'Montres' => ['Classiques', 'Sport'],
            'Accessoires' => ['Sacs', 'Ceintures', 'Bijoux'],
        ];

        foreach ($categories as $categoryName => $subCategories) {
            $category = new Category();
            $category->setName($categoryName);
            $this->entityManager->persist($category);
            $categoryMap[$categoryName] = $category;

            foreach ($subCategories as $subName) {
                $subCategory = new SubCategory();
                $subCategory->setName($subName);
                $subCategory->setCategory($category);
                $this->entityManager->persist($subCategory);
                $subCategoryMap[$subName] = $subCategory;
            }
        }

        $products = [
            [
                'name' => 'Sneakers Urban Pulse',
                'description' => 'Sneakers en cuir et mesh, semelle amortie et look urbain.',
                'price' => 59000,
                'stock' => 25,
                'image' => 'https://images.unsplash.com/photo-1635357423631-d82402183166?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'subs' => ['Sneakers'],
            ],
            [
                'name' => 'Derbies Cuir Noir',
                'description' => 'Derbies en cuir lisse, finitions elegantes et confort durable.',
                'price' => 85000,
                'stock' => 12,
                'image' => 'https://cdn.pixabay.com/photo/2021/03/08/12/06/oxford-shoes-6078951_1280.jpg',
                'subs' => ['Derbies'],
            ],
            [
                'name' => 'Bottes Chelsea',
                'description' => 'Bottes chelsea en cuir, silhouette classique et elastiques souples.',
                'price' => 99000,
                'stock' => 8,
                'image' => 'https://images.unsplash.com/photo-1571505385418-67bd85677abf?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'subs' => ['Bottes'],
            ],
            [
                'name' => 'Sandales Confort',
                'description' => 'Sandales legeres, brides ajustables et semelle antiderapante.',
                'price' => 32000,
                'stock' => 30,
                'image' => 'https://images.unsplash.com/photo-1603487742131-4160ec999306?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'subs' => ['Sandales'],
            ],
            [
                'name' => 'T-shirt Coton Premium',
                'description' => 'Coton peigne, coupe droite et toucher doux au quotidien.',
                'price' => 15000,
                'stock' => 40,
                'image' => 'https://cdn.pixabay.com/photo/2023/05/23/08/49/fashion-8012239_1280.jpg',
                'subs' => ['T-shirts'],
            ],
            [
                'name' => 'Chemise Oxford',
                'description' => 'Chemise oxford, col boutonne et finitions soignees.',
                'price' => 35000,
                'stock' => 24,
                'image' => 'https://images.unsplash.com/photo-1761896902115-49793a359daf?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'subs' => ['Chemises'],
            ],
            [
                'name' => 'Veste Blazer Structuree',
                'description' => 'Blazer structure, coupe moderne et doublure legere.',
                'price' => 120000,
                'stock' => 10,
                'image' => 'https://images.unsplash.com/photo-1535891169584-75bcaf12e964?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'subs' => ['Vestes'],
            ],
            [
                'name' => 'Pantalon Chino',
                'description' => 'Chino stretch, coupe slim et confort de mouvement.',
                'price' => 45000,
                'stock' => 18,
                'image' => 'https://cdn.pixabay.com/photo/2022/04/25/08/52/chino-7155415_1280.jpg',
                'subs' => ['Pantalons'],
            ],
            [
                'name' => 'Lunettes Solaires Riviera',
                'description' => 'Monture acetate, verres UV400 et look estival.',
                'price' => 68000,
                'stock' => 15,
                'image' => 'https://cdn.pixabay.com/photo/2018/03/14/23/35/sunglasses-3226707_1280.jpg',
                'subs' => ['Solaires'],
            ],
            [
                'name' => 'Lunettes Optiques Clarity',
                'description' => 'Monture fine et legere, style discret et elegant.',
                'price' => 52000,
                'stock' => 20,
                'image' => 'https://cdn.pixabay.com/photo/2017/08/03/13/15/flat-lay-2576201_1280.jpg',
                'subs' => ['Optiques'],
            ],
            [
                'name' => 'Montre Classique Heritage',
                'description' => 'Boitier acier, bracelet cuir et cadran sobre.',
                'price' => 150000,
                'stock' => 8,
                'image' => 'https://cdn.pixabay.com/photo/2018/12/23/17/59/watch-3891582_1280.jpg',
                'subs' => ['Classiques'],
            ],
            [
                'name' => 'Montre Sport Chrono',
                'description' => 'Chronographe sportif, etanche 100m et bracelet silicone.',
                'price' => 180000,
                'stock' => 6,
                'image' => 'https://cdn.pixabay.com/photo/2018/12/23/18/03/watch-3891591_1280.jpg',
                'subs' => ['Sport'],
            ],
            [
                'name' => 'Montre Minimalist',
                'description' => 'Design epure, boitier fin et bracelet acier.',
                'price' => 99000,
                'stock' => 12,
                'image' => 'https://cdn.pixabay.com/photo/2018/12/23/17/59/watch-3891582_1280.jpg',
                'subs' => ['Classiques'],
            ],
            [
                'name' => 'Sac Weekender',
                'description' => 'Sac de voyage compact, toile robuste et poches internes.',
                'price' => 110000,
                'stock' => 9,
                'image' => 'https://cdn.pixabay.com/photo/2022/08/05/05/24/duffel-7365977_1280.jpg',
                'subs' => ['Sacs'],
            ],
            [
                'name' => 'Ceinture Cuir',
                'description' => 'Ceinture en cuir graine, boucle metal brosse.',
                'price' => 22000,
                'stock' => 35,
                'image' => 'https://images.pexels.com/photos/71123/belts-leather-seam-sew-71123.jpeg?cs=srgb&dl=pexels-pixabay-71123.jpg&fm=jpg',
                'subs' => ['Ceintures'],
            ],
            [
                'name' => 'Portefeuille Compact',
                'description' => 'Portefeuille fin, cuir souple et format carte.',
                'price' => 26000,
                'stock' => 28,
                'image' => 'https://cdn.pixabay.com/photo/2020/03/28/13/24/wallet-4976998_1280.jpg',
                'subs' => ['Sacs'],
            ],
            [
                'name' => 'Bracelet Minimal',
                'description' => 'Bracelet acier, finition mate et style discret.',
                'price' => 18000,
                'stock' => 22,
                'image' => 'https://cdn.pixabay.com/photo/2018/07/17/17/39/bracelet-3544690_1280.jpg',
                'subs' => ['Bijoux'],
            ],
        ];

        foreach ($products as $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setDescription($data['description']);
            $product->setPrice($data['price']);
            $product->setStock($data['stock']);
            if (!empty($data['image'])) {
                $this->applyImage($product, $data['image']);
            }

            foreach ($data['subs'] as $subName) {
                if (isset($subCategoryMap[$subName])) {
                    $product->addSubCategory($subCategoryMap[$subName]);
                }
            }

            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();

        $io->success('Seed complete: categories, sub-categories, and products created.');

        return Command::SUCCESS;
    }
}
