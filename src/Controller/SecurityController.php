<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'app_api_')]
class SecurityController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private SerializerInterface $serializer)
    {
    }

    #[Route('/registration', name: 'registration', methods: 'POST')]
    /** @OA\Post(
     *     path="/api/registration",
     *     summary="Inscription d'un nouvel utilisateur",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données de l'utilisateur à inscrire",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="firstname", type="string", example="Bruno"),
     *             @OA\Property(property="lastname", type="string", example="Rivera"),
     *             @OA\Property(property="email", type="string", example="adresse@email.com"),
     *             @OA\Property(property="password", type="string", example="Mot de passe")
     *         )
     *      )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur inscrit avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user", type="string", example="Nom d'utilisateur"),
     *             @OA\Property(property="apiToken", type="string", example="31a023e212f116124a36af14ea0c1c3806eb9378"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string", example="ROLE_USER"))
     *         )
     *     )
     * )
     */
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $user->setCreatedAt(new DateTimeImmutable());

        $this->manager->persist($user);
        $this->manager->flush();
        return new JsonResponse(
            ['user'  => $user->getUserIdentifier(), 'apiToken' => $user->getApiToken(), 'roles' => $user->getRoles()],
            Response::HTTP_CREATED
        );
    }
    #[Route('/login', name: 'login', methods: 'POST')]
    /** @OA\Post(
     *     path="/api/login",
     *     summary="Connecter un utilisateur",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données de l’utilisateur pour se connecter",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="username", type="string", example="adresse@email.com"),
     *             @OA\Property(property="password", type="string", example="Mot de passe")
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Connexion réussie",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user", type="string", example="Nom d'utilisateur"),
     *             @OA\Property(property="apiToken", type="string", example="31a023e212f116124a36af14ea0c1c3806eb9378"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string", example="ROLE_USER"))
     *          )
     *      )
     *   )
     */
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'Missing credentials'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'user' => $user->getUserIdentifier(),
            'apiToken' => $user->getApiToken(),
            'roles' => $user->getRoles(),
        ]);
    }
    #[Route('/account/me', name: 'me', methods: 'GET')]
    /** @OA\Get(
     *     path="/api/account/me",
     *     summary="Récupérer les Information de l'utilisateur",
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Retour tous les champs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user", type="string", example="Nom d'utilisateur"),
     *             @OA\Property(property="apiToken", type="string", example="31a023e212f116124a36af14ea0c1c3806eb9378"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string", example="ROLE_USER"))
     *          ),
     *         @OA\Response(
     *             response=401,
     *             description="User not authenticated"),
     *      )
     *   )
     */
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        $responseData = $this->serializer->serialize($user, 'json');
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }
    #[Route('/account/edit', name: 'edit', methods: 'PUT')]
    /**
     * @OA\Put(
     *     path="/api/account/edit",
     *     summary="Modifier son compte utilisateur",
     *     ),
     * @OA\Requestbody(
     *     required= true,
     *     description="Nouvelles données éventuelle de l'utilisateur à mettre à jour",
     *     @OA\Jsoncontent(
     *         type="objet",
     *         @OA\Property(property="firstname", type="string", example="Benoit"),
     *         @OA\Property(property="lastname", type="string", example="Benor")
     *         )
     *     ),
     * @OA\Response(
     *          response=204,
     *          description="Utilisateur modifié avec succès"
     *      ),
     * @OA\Response(
     *          response=404,
     *          description="Utilisateur non connecté"
     *      )
     * )
     */

    public function edit(Request $request): JsonResponse
    {
        $user = $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $this->getUser()],
        );
        $user->setUpdatedAt(new DateTimeImmutable());

        if (isset($request->toArray()['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
        }

        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}