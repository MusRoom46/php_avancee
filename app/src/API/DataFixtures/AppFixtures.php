<?php

namespace App\API\DataFixtures;

use App\API\Entity\Comment;
use App\API\Entity\Follow;
use App\API\Entity\Likes;
use App\API\Entity\Tweet;
use App\API\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Exemples de contenus français
        $tweetContents = [
            "Quelqu’un comprend vraiment le routing Symfony ? 😅",
            "Premier test avec Faker, c’est trop bien !",
            "Le café est essentiel pour coder proprement ☕",
            "J’ai cassé ma base de données… encore une fois.",
            "Symfony ou Laravel ? Vos avis ?",
            "Il pleut mais au moins mon code fonctionne.",
            "Prochain objectif : sécuriser mon API avec JWT.",
            "J’adore ce framework, vraiment bien pensé !",
            "L’intégration de Doctrine est magique 🔮",
            "J’ai mis 3h à trouver une faute de frappe…",
            "CSS me fait plus peur que SQL honnêtement.",
            "La méthode magique __toString m’a encore eu !",
            "Qui veut tester mon projet Symfony ? 🧪",
            "Les fixtures c’est génial pour bosser en local.",
            "Encore un commit ‘fix bug’… 🤦",
        ];

        $commentContents = [
            "Très bon point !",
            "Je suis d'accord avec toi.",
            "Tu peux expliquer un peu plus ?",
            "Pas mal, mais ça pourrait être optimisé.",
            "Merci pour l'astuce !",
            "Haha, tellement vrai !",
            "C'est exactement ce que je pense.",
            "Je ne suis pas convaincu.",
            "Tu aurais une source ?",
            "GG pour ton projet 👏",
            "Intéressant, je vais tester ça.",
            "Symfony c’est top !",
            "Laravel reste plus intuitif je trouve.",
            "Doctrine parfois me perd complètement…",
            "J’ai eu le même problème hier !",
        ];

        // Créer des utilisateurs
        $users = [];
        for ($i = 0; $i < 10; $i++) {
            $user = new Users();
            $user->setPseudo($faker->userName)
                ->setEmail($faker->email)
                ->setMdp(password_hash('password', PASSWORD_BCRYPT))
                ->setAvatar($faker->imageUrl(100, 100, 'people'))
                ->setDateCreation($faker->dateTimeThisYear);
            $manager->persist($user);
            $users[] = $user;
        }

        // Créer des tweets
        $tweets = [];
        for ($i = 0; $i < 40; $i++) {
            $tweet = new Tweet();
            $tweet->setContenu($faker->randomElement($tweetContents))
                ->setDate($faker->dateTimeThisMonth)
                ->setUser($faker->randomElement($users));
            $manager->persist($tweet);
            $tweets[] = $tweet;
        }

        // Créer des commentaires
        for ($i = 0; $i < 80; $i++) {
            $comment = new Comment();
            $comment->setContenu($faker->randomElement($commentContents))
                ->setDate($faker->dateTimeThisMonth)
                ->setTweet($faker->randomElement($tweets))
                ->setUser($faker->randomElement($users));
            $manager->persist($comment);
        }

        // Créer des likes
        for ($i = 0; $i < 100; $i++) {
            $like = new Likes();
            $like->setDate($faker->dateTimeThisMonth)
                ->setTweet($faker->randomElement($tweets))
                ->setUser($faker->randomElement($users));
            $manager->persist($like);
        }

        // Créer des follows (évite de suivre soi-même)
        for ($i = 0; $i < 30; $i++) {
            $follower = $faker->randomElement($users);
            $followed = $faker->randomElement($users);

            if ($follower !== $followed) {
                $follow = new Follow();
                $follow->setDate($faker->dateTimeThisYear)
                    ->setUser($follower)
                    ->setUserSuivi($followed);
                $manager->persist($follow);
            }
        }

        $manager->flush();
    }
}
