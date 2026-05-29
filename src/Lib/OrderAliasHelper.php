<?php

declare( strict_types=1 );

namespace Ilabs\Inpost_Pay\Lib;

use Ilabs\Inpost_Pay\EntityLayer\Entity\OrderAliasEntity;
use Ilabs\Inpost_Pay\EntityLayer\Repository\OrderAliasRepository;
use Ilabs\Inpost_Pay\Lib\exception\AliasAlreadyExistsException;
use Ilabs\Inpost_Pay\Lib\exception\InvalidAliasException;
use Exception;
use function Ilabs\Inpost_Pay\inpost_pay_container;

class OrderAliasHelper {

	private static ?OrderAliasRepository $repository = null;

	private static function get_repository(): OrderAliasRepository {
		if ( null === self::$repository ) {
			$container        = inpost_pay_container();
			self::$repository = $container->get( OrderAliasRepository::SERVICE_KEY );
		}

		return self::$repository;
	}

	/**
	 * @throws InvalidAliasException
	 * @throws AliasAlreadyExistsException
	 * @throws Exception
	 */
	public static function createAlias( string $aliasId, int $orderId ): void {
		$aliasId = trim( $aliasId );

		if ( '' === $aliasId || strlen( $aliasId ) > 64 ) {
			throw new InvalidAliasException( $aliasId );
		}

		$repo     = self::get_repository();
		$existing = $repo->find_one_by( array( 'alias_order_id' => $aliasId ) );

		if ( $existing && $existing->get_order_id() ) {
			throw new AliasAlreadyExistsException( $aliasId );
		}

		$entity = new OrderAliasEntity();
		$entity->set_alias_order_id( $aliasId );
		$entity->set_order_id( $orderId );
		$entity->set_created_at( current_time( 'mysql' ) );

		$repo->save( $entity );
	}

	public static function getRealId( string $aliasId ): ?int {
		$repo   = self::get_repository();
		$entity = $repo->find_one_by( array( 'alias_order_id' => $aliasId ) );

		return $entity ? $entity->get_order_id() : null;
	}

	public static function getAlias( int $orderId ): ?string {
		$repo   = self::get_repository();
		$entity = $repo->find_one_by( array( 'order_id' => $orderId ) );

		return $entity ? $entity->get_alias_order_id() : null;
	}

	public static function resolve( $id ) {
		$realId = self::getRealId( (string) $id );

		if ( null !== $realId ) {
			return wc_get_order( $realId );
		}

		$order = wc_get_order( $id );
		return $order !== false ? $order : null;
	}
}
