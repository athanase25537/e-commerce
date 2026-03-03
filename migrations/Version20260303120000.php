<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260303120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed 30 products per category (vetements, nounours, montres) with image URLs.';
    }

    public function up(Schema $schema): void
    {
        $categories = [
            'Vetements' => ['T-shirts', 'Chemises', 'Vestes', 'Pantalons', 'Robes', 'Pulls'],
            'Nounours' => ['Classiques', 'Geants', 'Mini', 'Artisanal', 'Vintage', 'Cadeaux'],
            'Montres' => ['Classiques', 'Sport', 'Connectees', 'Minimalistes', 'Automatiques', 'Chronographes'],
        ];

        foreach (array_keys($categories) as $categoryName) {
            $this->connection->executeStatement(
                'INSERT INTO category (name) VALUES (?) ON CONFLICT (name) DO NOTHING',
                [$categoryName]
            );
        }

        $categoryIds = [];
        foreach (array_keys($categories) as $categoryName) {
            $categoryId = $this->connection->fetchOne('SELECT id FROM category WHERE name = ?', [$categoryName]);
            if (!$categoryId) {
                throw new \RuntimeException('Category not found: ' . $categoryName);
            }
            $categoryIds[$categoryName] = (int) $categoryId;
        }

        foreach ($categories as $categoryName => $subCategories) {
            $categoryId = $categoryIds[$categoryName];
            foreach ($subCategories as $subCategoryName) {
                $this->connection->executeStatement(
                    "INSERT INTO sub_category (name, category_id)\n                    SELECT ?, ?\n                    WHERE NOT EXISTS (\n                        SELECT 1 FROM sub_category WHERE name = ? AND category_id = ?\n                    )",
                    [$subCategoryName, $categoryId, $subCategoryName, $categoryId]
                );
            }
        }

        $imageSets = [
            'Vetements' => [
                'https://images.unsplash.com/photo-1567113463300-102a7eb3cb26?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'https://images.unsplash.com/photo-1545007805-a44ee83438fa?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'https://images.unsplash.com/photo-1562986398-ef6efbbc9537?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'https://images.unsplash.com/photo-1657981190914-e7bc1cba612e?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'https://images.unsplash.com/photo-1451930764750-08054b9e3910?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
            ],
            'Nounours' => [
                'https://images.unsplash.com/photo-1708360459857-9563d80d3acb?fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'https://images.unsplash.com/photo-1577568315884-9372fbdd39f4?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'https://images.unsplash.com/photo-1574466853640-8557d3e4ab2c?fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'https://cdn.pixabay.com/photo/2019/10/30/17/10/teddy-4590050_1280.jpg',
                'https://cdn.pixabay.com/photo/2017/10/16/02/49/teddy-bear-2855982_1280.jpg',
                'https://cdn.pixabay.com/photo/2018/05/16/13/31/teddy-3405768_1280.jpg',
            ],
            'Montres' => [
                'https://images.unsplash.com/photo-1748397653605-a8cba64d409e?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'https://images.unsplash.com/photo-1754606581494-45e91e678edc?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'https://images.unsplash.com/photo-1708247804800-bf34a8a24b38?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'https://images.unsplash.com/photo-1647336399369-ab0a52f37752?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'https://images.unsplash.com/photo-1657159810148-f6a1f3d74f7e?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'https://images.unsplash.com/photo-1690343430066-d4634276895d?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'https://images.unsplash.com/photo-1690469150030-801f3c73cba8?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
                'https://images.unsplash.com/photo-1514218842929-d6b0d653a623?auto=format&fit=crop&fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&ixlib=rb-4.1.0&q=60&w=3000',
            ],
        ];

        $this->seedVetements($categoryIds['Vetements'], $categories['Vetements'], $imageSets['Vetements']);
        $this->seedNounours($categoryIds['Nounours'], $categories['Nounours'], $imageSets['Nounours']);
        $this->seedMontres($categoryIds['Montres'], $categories['Montres'], $imageSets['Montres']);
    }

    public function down(Schema $schema): void
    {
        $categoryNames = ['Vetements', 'Nounours', 'Montres'];
        foreach ($categoryNames as $categoryName) {
            $categoryId = $this->connection->fetchOne('SELECT id FROM category WHERE name = ?', [$categoryName]);
            if (!$categoryId) {
                continue;
            }

            $this->connection->executeStatement(
                "DELETE FROM product p\n                USING product_sub_category psc, sub_category sc\n                WHERE p.id = psc.product_id\n                  AND psc.sub_category_id = sc.id\n                  AND sc.category_id = ?",
                [(int) $categoryId]
            );

            $this->connection->executeStatement('DELETE FROM sub_category WHERE category_id = ?', [(int) $categoryId]);
            $this->connection->executeStatement('DELETE FROM category WHERE id = ?', [(int) $categoryId]);
        }
    }

    private function seedVetements(int $categoryId, array $subCategories, array $images): void
    {
        $types = ['T-shirt', 'Chemise', 'Veste', 'Pantalon', 'Robe', 'Pull'];
        $materials = ['coton', 'lin', 'denim', 'laine', 'jersey', 'twill'];
        $colors = ['noir', 'marine', 'sable', 'olive', 'bordeaux', 'gris'];

        for ($i = 1; $i <= 30; $i++) {
            $index = ($i - 1) % count($types);
            $type = $types[$index];
            $subCategoryId = $this->getSubCategoryId($categoryId, $subCategories[$index]);
            $color = ucfirst($colors[$i % count($colors)]);
            $name = sprintf('%s %s %02d', $type, $color, $i);
            $description = sprintf('%s %s, coupe moderne et tissu respirant.', $type, $materials[$i % count($materials)]);
            $price = 12000 + ($i * 1500);
            $stock = 10 + ($i % 20);
            $image = $images[$i % count($images)];

            $productId = $this->insertProduct($name, $description, $price, $stock, $image);
            $this->linkProductToSubCategory($productId, $subCategoryId);
        }
    }

    private function seedNounours(int $categoryId, array $subCategories, array $images): void
    {
        $styles = ['Classique', 'Geant', 'Mini', 'Artisanal', 'Vintage', 'Cadeau'];
        $finishes = ['peluche douce', 'coutures solides', 'texture moelleuse', 'rembourrage ferme', 'tissu velours', 'broderie fine'];

        for ($i = 1; $i <= 30; $i++) {
            $index = ($i - 1) % count($styles);
            $style = $styles[$index];
            $subCategoryId = $this->getSubCategoryId($categoryId, $subCategories[$index]);
            $name = sprintf('Nounours %s %02d', $style, $i);
            $description = sprintf('Nounours %s, %s, ideal pour cadeau.', strtolower($style), $finishes[$i % count($finishes)]);
            $price = 8000 + ($i * 700);
            $stock = 15 + ($i % 30);
            $image = $images[$i % count($images)];

            $productId = $this->insertProduct($name, $description, $price, $stock, $image);
            $this->linkProductToSubCategory($productId, $subCategoryId);
        }
    }

    private function seedMontres(int $categoryId, array $subCategories, array $images): void
    {
        $styles = ['Classique', 'Sport', 'Connectee', 'Minimaliste', 'Automatique', 'Chronographe'];
        $materials = ['acier', 'titane', 'ceramique', 'cuir', 'silicone', 'maille'];

        for ($i = 1; $i <= 30; $i++) {
            $index = ($i - 1) % count($styles);
            $style = $styles[$index];
            $subCategoryId = $this->getSubCategoryId($categoryId, $subCategories[$index]);
            $name = sprintf('Montre %s %02d', $style, $i);
            $description = sprintf('Boitier %s, verre mineral, bracelet confortable.', $materials[$i % count($materials)]);
            $price = 65000 + ($i * 3500);
            $stock = 5 + ($i % 15);
            $image = $images[$i % count($images)];

            $productId = $this->insertProduct($name, $description, $price, $stock, $image);
            $this->linkProductToSubCategory($productId, $subCategoryId);
        }
    }

    private function insertProduct(string $name, string $description, int $price, int $stock, string $image): int
    {
        $productId = $this->connection->fetchOne(
            "INSERT INTO product (name, description, price, image, image_mime_type, stock)\n            VALUES (?, ?, ?, convert_to(?, 'UTF8'), NULL, ?)\n            RETURNING id",
            [$name, $description, $price, $image, $stock]
        );

        if (!$productId) {
            throw new \RuntimeException('Failed to insert product: ' . $name);
        }

        return (int) $productId;
    }

    private function linkProductToSubCategory(int $productId, int $subCategoryId): void
    {
        $this->connection->executeStatement(
            'INSERT INTO product_sub_category (product_id, sub_category_id) VALUES (?, ?)',
            [$productId, $subCategoryId]
        );
    }

    private function getSubCategoryId(int $categoryId, string $name): int
    {
        $subCategoryId = $this->connection->fetchOne(
            'SELECT id FROM sub_category WHERE name = ? AND category_id = ?',
            [$name, $categoryId]
        );

        if (!$subCategoryId) {
            throw new \RuntimeException('Sub-category not found: ' . $name);
        }

        return (int) $subCategoryId;
    }
}
