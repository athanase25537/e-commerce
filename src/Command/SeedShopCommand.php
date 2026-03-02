<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\SubCategory;
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
        $platform = $connection->getDatabasePlatform()->getName();
        if ($platform === 'sqlite') {
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

        if ($platform === 'sqlite') {
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
                'subs' => ['Sneakers'],
            ],
            [
                'name' => 'Derbies Cuir Noir',
                'description' => 'Derbies en cuir lisse, finitions elegantes et confort durable.',
                'price' => 85000,
                'stock' => 12,
                'subs' => ['Derbies'],
            ],
            [
                'name' => 'Bottes Chelsea',
                'description' => 'Bottes chelsea en cuir, silhouette classique et elastiques souples.',
                'price' => 99000,
                'stock' => 8,
                'subs' => ['Bottes'],
            ],
            [
                'name' => 'Sandales Confort',
                'description' => 'Sandales legeres, brides ajustables et semelle antiderapante.',
                'price' => 32000,
                'stock' => 30,
                'subs' => ['Sandales'],
            ],
            [
                'name' => 'T-shirt Coton Premium',
                'description' => 'Coton peigne, coupe droite et toucher doux au quotidien.',
                'price' => 15000,
                'stock' => 40,
                'subs' => ['T-shirts'],
            ],
            [
                'name' => 'Chemise Oxford',
                'description' => 'Chemise oxford, col boutonne et finitions soignees.',
                'price' => 35000,
                'stock' => 24,
                'subs' => ['Chemises'],
            ],
            [
                'name' => 'Veste Blazer Structuree',
                'description' => 'Blazer structure, coupe moderne et doublure legere.',
                'price' => 120000,
                'stock' => 10,
                'subs' => ['Vestes'],
            ],
            [
                'name' => 'Pantalon Chino',
                'description' => 'Chino stretch, coupe slim et confort de mouvement.',
                'price' => 45000,
                'stock' => 18,
                'subs' => ['Pantalons'],
            ],
            [
                'name' => 'Lunettes Solaires Riviera',
                'description' => 'Monture acetate, verres UV400 et look estival.',
                'price' => 68000,
                'stock' => 15,
                'subs' => ['Solaires'],
            ],
            [
                'name' => 'Lunettes Optiques Clarity',
                'description' => 'Monture fine et legere, style discret et elegant.',
                'price' => 52000,
                'stock' => 20,
                'subs' => ['Optiques'],
            ],
            [
                'name' => 'Montre Classique Heritage',
                'description' => 'Boitier acier, bracelet cuir et cadran sobre.',
                'price' => 150000,
                'stock' => 8,
                'subs' => ['Classiques'],
            ],
            [
                'name' => 'Montre Sport Chrono',
                'description' => 'Chronographe sportif, etanche 100m et bracelet silicone.',
                'price' => 180000,
                'stock' => 6,
                'subs' => ['Sport'],
            ],
            [
                'name' => 'Montre Minimalist',
                'description' => 'Design epure, boitier fin et bracelet acier.',
                'price' => 99000,
                'stock' => 12,
                'subs' => ['Classiques'],
            ],
            [
                'name' => 'Sac Weekender',
                'description' => 'Sac de voyage compact, toile robuste et poches internes.',
                'price' => 110000,
                'stock' => 9,
                'subs' => ['Sacs'],
            ],
            [
                'name' => 'Ceinture Cuir',
                'description' => 'Ceinture en cuir graine, boucle metal brosse.',
                'price' => 22000,
                'stock' => 35,
                'subs' => ['Ceintures'],
            ],
            [
                'name' => 'Portefeuille Compact',
                'description' => 'Portefeuille fin, cuir souple et format carte.',
                'price' => 26000,
                'stock' => 28,
                'subs' => ['Sacs'],
            ],
            [
                'name' => 'Bracelet Minimal',
                'description' => 'Bracelet acier, finition mate et style discret.',
                'price' => 18000,
                'stock' => 22,
                'subs' => ['Bijoux'],
            ],
        ];

        foreach ($products as $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setDescription($data['description']);
            $product->setPrice($data['price']);
            $product->setStock($data['stock']);

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
