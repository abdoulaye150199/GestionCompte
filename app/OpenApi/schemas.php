<?php

/**
 * @OA\Schema(
 *     schema="Compte",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="numeroCompte", type="string", example="C00123456"),
 *     @OA\Property(property="titulaire", type="string", example="Amadou Diallo"),
 *     @OA\Property(property="type", type="string", enum={"epargne", "cheque"}),
 *     @OA\Property(property="solde", type="number", format="float", example=1250000),
 *     @OA\Property(property="devise", type="string", example="FCFA"),
 *     @OA\Property(property="dateCreation", type="string", format="date-time"),
 *     @OA\Property(property="statut", type="string", enum={"actif", "bloque", "ferme"}),
 *     @OA\Property(property="dateFinBlocage", type="string", format="date-time", nullable=true),
 *     @OA\Property(
 *         property="metadonnees",
 *         type="object",
 *         @OA\Property(property="derniereModification", type="string", format="date-time"),
 *         @OA\Property(property="version", type="integer", example=1)
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     @OA\Property(property="currentPage", type="integer"),
 *     @OA\Property(property="totalPages", type="integer"),
 *     @OA\Property(property="totalItems", type="integer"),
 *     @OA\Property(property="itemsPerPage", type="integer"),
 *     @OA\Property(property="hasNext", type="boolean"),
 *     @OA\Property(property="hasPrevious", type="boolean")
 * )
 *
 * @OA\Schema(
 *     schema="ArchivedCompte",
 *     @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="numeroCompte", type="string", example="C00123456"),
 *     @OA\Property(property="userId", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="type", type="string", enum={"epargne", "cheque"}),
 *     @OA\Property(property="solde", type="number", format="float", example=1250000),
 *     @OA\Property(property="devise", type="string", example="FCFA"),
 *     @OA\Property(property="statut", type="string", enum={"actif", "bloque", "ferme"}),
 *     @OA\Property(property="dateCreation", type="string", format="date-time"),
 *     @OA\Property(property="dateFinBlocage", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="dateArchivage", type="string", format="date-time"),
 *     @OA\Property(property="raisonArchivage", type="string", example="Blocage expiré"),
 *     @OA\Property(property="metadonnees", type="object"),
 *     @OA\Property(property="archivedAt", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="ApiResponse",
 *     @OA\Property(property="success", type="boolean"),
 *     @OA\Property(property="message", type="string"),
 *     @OA\Property(property="data", type="object")
 * )
 */